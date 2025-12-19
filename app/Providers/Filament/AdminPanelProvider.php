<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use App\Models\Dashboard;
use Filament\PanelProvider;
use App\Filament\Pages\Settings;
use App\Filament\Pages\ViewLogs;
use Filament\Navigation\MenuItem;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Blade;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Resources\AssetResource;
use App\Filament\Resources\FilterResource;
use App\Filament\Resources\WidgetResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Resources\EnvelopResource;
use App\Filament\Resources\ValuationResource;
use App\Filament\Resources\CurrencyResource;
use App\Filament\Resources\TaxonomyResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\DashboardResource;
use App\Filament\Resources\AssetClassResource;
use Resma\FilamentAwinTheme\FilamentAwinTheme;
use App\Filament\Resources\EnvelopTypeResource;
use App\Filament\Resources\TaxonomyTagResource;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\NotificationResource;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Resources\ValuationUpdateResource;
use App\Filament\Resources\ValuationHistoryResource;
use Filament\FontProviders\SpatieGoogleFontProvider;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Resources\Schedules\ScheduleResource;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use ApollocatDev\FilamentSettings\Filament\Resources\SettingResource;
use App\Filament\Resources\CryptoPoolResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->profile()
            ->brandLogo(asset('images/patrimeo_horizontal_small.png'))
            ->darkModeBrandLogo(asset('images/patrimeo_horizontal_small_dark.png'))
            ->brandLogoHeight('2rem')

            // ->font('Noto Sans')
            ->font('Montserrat', provider: SpatieGoogleFontProvider::class)
            ->colors([
                'primary' => Color::Sky
            ])
            ->plugins([
                FilamentApexChartsPlugin::make(),
            ])
            ->maxContentWidth(Width::Full)
            ->topNavigation()
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make('Dashboards')->items(
                        $this->getUserDashboardNavigationItems()
                    ),
                    NavigationGroup::make('Portfolio')->items([
                        ...AssetResource::getNavigationItems(),
                        ...TransactionResource::getNavigationItems(),
                    ]),
                    NavigationGroup::make('Valuations')->items([
                        ...ValuationResource::getNavigationItems(),
                        ...ValuationUpdateResource::getNavigationItems(),
                        ...ValuationHistoryResource::getNavigationItems(),
                    ]),
                    NavigationGroup::make('Tools')->items([
                        ...CryptoPoolResource::getNavigationItems(),
                    ]),
                    NavigationGroup::make('Configuration')->items([
                        ...AssetClassResource::getNavigationItems(),
                        ...EnvelopResource::getNavigationItems(),
                        ...EnvelopTypeResource::getNavigationItems(),
                        ...CurrencyResource::getNavigationItems(),
                        ...DashboardResource::getNavigationItems(),
                        ...WidgetResource::getNavigationItems(),
                        ...TaxonomyResource::getNavigationItems(),
                        ...TaxonomyTagResource::getNavigationItems(),
                        ...FilterResource::getNavigationItems(),
                        ...ScheduleResource::getNavigationItems(),
                        ...NotificationResource::getNavigationItems(),
                        ...ViewLogs::getNavigationItems(),
                        // ...Settings::getNavigationItems(),
                        ...SettingResource::getNavigationItems(),
                    ])->extraTopbarAttributes(['class' => 'custom-separator']),
                ]);
            })
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => Blade::render('@livewire(\'version-display\')')
            )
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => Blade::render('@livewire(\'dashboard-refresh-button\')')
            )
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => Blade::render('@livewire(\'valuations-update-button\')')
            )
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => Blade::render('@livewire(\'integrity-dropdown\')')
            )
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => Blade::render('@livewire(\'notification-dropdown\')')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                SettingResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->homeUrl(function () {
                $dashboard = Dashboard::where(['default' => true, 'active' => true])->first();
                if (!$dashboard) {
                    // Fallback to first active dashboard or admin dashboard
                    $dashboard = Dashboard::where('active', true)->first();
                    if (!$dashboard) {
                        return '/admin';
                    }
                }
                return '/admin/user-dashboard/' . $dashboard->id;
            })
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            // ->navigationGroups([
            //     'Dashboards',
            //     'Portfolio',
            //     'Valuations',
            //     'Configuration',
            // ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\OverrideSessionConfig::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function getUserDashboardNavigationItems()
    {
        $dashboards = Dashboard::where('active', true)->get();
        $items = [];
        foreach ($dashboards as $dashboard) {
            $items[] = NavigationItem::make($dashboard->navigation_title)
                ->url('/admin/user-dashboard/' . $dashboard->id);
        }
        return $items;
    }
}
