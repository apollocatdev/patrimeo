<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Widget;
// Setting model removed - using filament-typehint-settings
use App\Models\Valuation;
use App\Models\WidgetStat;
use Filament\Support\RawJs;
use App\Charts\AbstractChart;
use App\Enums\Widgets\WidgetType;
use App\Helpers\WidgetHelperTrait;
use App\Models\WidgetStatOverview;
use App\Settings\VariousSettings;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DynamicChart extends ApexChartWidget
{
    // use WidgetHelperTrait;

    public array $stat;
    // protected int | string | array $columnSpan =  3;

    // protected function getPollingInterval(): ?string
    // {
    //     return $this->widget->polling === null ? null : $this->widget->polling . 's';
    // }

    protected function getHeading(): string
    {
        return $this->stat['label'];
    }

    protected function getOptions(): array
    {
        // \Log::debug(print_r($this->stat, true));
        return $this->stat['options'];
    }

    // public function getDescription(): ?string
    // {
    //     return $this->stat['description'];
    // }
    public function getColumnSpan(): int|string|array
    {
        return $this->stat['column_span'] ?? 'full';
    }

    protected function extraJsOptions(): ?RawJs
    {
        $settings = VariousSettings::get();
        $palette = $settings->chartsTheme;
        $options = "{ theme: { palette: '" . $palette . "' }}";
        return RawJs::make($options);
    }
}
