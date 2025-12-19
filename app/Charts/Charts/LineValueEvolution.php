<?php

namespace App\Charts\Charts;

use App\Enums\Widgets\WidgetType;
use App\Helpers\Portfolio;
use App\Charts\AbstractChart;
use App\Models\ValuationHistory;
use App\Helpers\PortfolioCompute;
use App\Enums\Widgets\WidgetTimePeriod;
use App\Enums\Widgets\WidgetTimeInterval;
use Filament\Forms\Components\Select;

class LineValueEvolution extends AbstractChart
{
    public static WidgetType $type = WidgetType::CHART_LINE_VALUE_EVOLUTION;

    protected function compute()
    {
        $data = [];
        $sections = $this->timeSeriesSections();
        foreach ($sections as $section) {
            $x = $this->timeSerieFormatDateLabel($section[0]);
            $y = (new PortfolioCompute($this->widget->filters))->portfolioValue($section[0]);
            $data[] = [
                'x' => $x,
                'y' => $y,
            ];
        }

        $this->options = [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [
                [
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
            ],
            'yaxis' => [
                'forceNiceScale' => true,
            ],
        ];
    }


    public static function form(): array
    {
        return [
            'since' => Select::make('since')
                ->label(__('Since'))
                ->options(WidgetTimePeriod::class)
                ->required(),
            'interval' => Select::make('interval')
                ->label(__('Interval'))
                ->options(WidgetTimeInterval::class)
                ->required(),
        ];
    }
}
