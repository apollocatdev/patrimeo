<?php

namespace App\Filament\Pages;

use Illuminate\Support\Facades\Auth;
use App\Models\Widget;
// Setting model removed - using filament-typehint-settings
use Filament\Pages\Page;
use App\Enums\Widgets\WidgetType;
use App\Models\Dashboard;
use App\Models\WidgetStat;
use App\Models\WidgetChart;
use App\Models\WidgetStatOverview;
use App\Filament\Widgets\DynamicStat;
use Illuminate\Support\Facades\Route;
use App\Filament\Widgets\DynamicChart;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Collection;
use App\Models\DashboardCache;
use Illuminate\Support\Facades\Log;
use App\Settings\VariousSettings;
use App\Helpers\Logs\LogDashboards;

class UserDashboard extends \Filament\Pages\Dashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string $routePath = 'user-dashboard/{dashboardId}';
    // protected int | string | array $columnSpan = 'full';

    protected ?Dashboard $dashboard = null;
    protected ?Collection $widgets = null;

    public function mount($dashboardId)
    {
        $this->dashboard = Dashboard::with(['widgets' => function ($q) {
            $q->orderBy('sort', 'asc');
        }, 'widgets.filters'])->findOrFail($dashboardId);
        $this->widgets = $this->dashboard->widgets;

        // $this->dashboard = Dashboard::with([gets'getStats'])->find($dashboardId);
        self::$navigationIcon = $this->dashboard->navigation_icon;

        // Log dashboard load
        LogDashboards::info("Dashboard loaded: {$this->dashboard->navigation_title} with {$this->widgets->count()} widgets");
    }

    public function getTitle(): string
    {
        return $this->dashboard->navigation_title;
    }

    public function getColumns(): int|array
    {
        return $this->dashboard->n_columns;
    }


    public function getWidgets(): array
    {
        $widgets = [];
        $statWidgets = [];
        $renderedWidgets = [];

        // Load dashboard cache settings
        $cacheSettings = VariousSettings::get();
        $cacheEnabled = $cacheSettings->dashboardCaching;
        $cacheExpiration = $cacheSettings->dashboardCachingTime;

        // Log if cache is enabled or disabled
        if ($cacheEnabled) {
            LogDashboards::info("Dashboard cache enabled (expiration: {$cacheExpiration} minutes)");
        } else {
            LogDashboards::info("Dashboard cache disabled");
        }

        foreach ($this->widgets as $widget) {
            $cache = null;

            if ($cacheEnabled) {
                $expiredCache = DashboardCache::where('dashboard_widget_id', $widget->id)
                    ->where('user_id', Auth::id())
                    ->where('expires_at', '<=', now())
                    ->first();

                if ($expiredCache) {
                    LogDashboards::info("Widget cache expired: {$widget->id}");
                    $expiredCache->delete();
                }

                $cache = DashboardCache::where('dashboard_widget_id', $widget->id)
                    ->where('user_id', Auth::id())
                    ->where('expires_at', '>', now())
                    ->first();
            }

            // Log cache status
            if ($cache === null) {
                LogDashboards::debug("Widget cache miss: {$widget->id}");
            } else {
                LogDashboards::debug("Widget cache hit: {$widget->id}");
            }
            if (str_starts_with($widget->type->value, 'stat_')) {
                $widgetData = $cache === null ? $this->getWidgetClass($widget, 'stat') : $cache->data;
                $statWidgets[] = $widgetData;
            } else {
                if (count($statWidgets) > 0) {
                    $widgets[] = $statWidgets;
                    $statWidgets = [];
                }
                $widgetData = $cache === null ? $this->getWidgetClass($widget, 'chart') : $cache->data;
                $widgets[] = $widgetData;
            }
            if ($cache === null && $cacheEnabled) {
                DashboardCache::updateOrCreate(
                    ['dashboard_widget_id' => $widget->id, 'user_id' => Auth::id()],
                    ['data' => $widgetData, 'expires_at' => now()->addMinutes($cacheExpiration)]
                );

                // Log widget calculation
                LogDashboards::debug("Widget calculated: {$widget->id} ({$widget->type->value})");
            }
        }
        if (count($statWidgets) > 0) {
            $widgets[] = $statWidgets;
        }

        foreach ($widgets as $widget) {
            if (! isset($widget['options'])) {
                $renderedWidgets[] = DynamicStat::make(['stats' => $widget]);
            } else {
                $renderedWidgets[] = DynamicChart::make(['stat' => $widget]);
            }
        }

        return $renderedWidgets;
    }


    protected function getWidgetClass(Widget $widget, string $type)
    {
        $statsClasses = $type === 'stat' ? glob(app_path('Charts/Stats/*.php')) : glob(app_path('Charts/Charts/*.php'));
        foreach ($statsClasses as $file) {
            $className = $type === 'stat' ? 'App\\Charts\\Stats\\' . pathinfo($file, PATHINFO_FILENAME) : 'App\\Charts\\Charts\\' . pathinfo($file, PATHINFO_FILENAME);
            if (class_exists($className) && property_exists($className, 'type')) {
                /** @var class-string<\App\Charts\AbstractChart|\App\Charts\AbstractStat> $className */
                $reflection = new \ReflectionClass($className);
                $typeProperty = $reflection->getStaticPropertyValue('type');
                if ($typeProperty->value === $widget->type->value) {
                    /** @var \App\Charts\AbstractChart|\App\Charts\AbstractStat $instance */
                    $instance = new $className($widget);
                    // $instance->compute();
                    $data = $instance->toArray();
                    $data['column_span'] = $widget->pivot->column_span ?? 'full';
                    return $data;
                }
            }
        }
        return null;
    }
}
