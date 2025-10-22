<?php

namespace App\Charts\Charts;

use App\Enums\Widgets\WidgetType;
use App\Charts\AbstractChart;
use App\Helpers\PortfolioCompute;
use App\Enums\Widgets\WidgetTimePeriod;
use App\Enums\Widgets\WidgetTimeInterval;
use App\Enums\Widgets\WidgetPerformanceAlgorithm;
use Filament\Forms\Components\Select;

class BarPerformanceEvolution extends AbstractChart
{
    public static WidgetType $type = WidgetType::CHART_BAR_PERFORMANCE_EVOLUTION;

    protected function compute()
    {
        $algorithm = $this->widget->parameters['algorithm'];
        $data = [];

        $sections = $this->timeSeriesSections();
        foreach ($sections as $section) {
            // $x = $section[0]->format('U') * 1000; 
            $x = $this->timeSerieFormatDateLabel($section[0]);
            if ($algorithm === 'TWR') {
                $y = (new PortfolioCompute($this->widget->filters))->getTWRPerformance($section[0], $section[1]);
            } elseif ($algorithm === 'MWR') {
                $y = (new PortfolioCompute($this->widget->filters))->getMWRPerformance($section[0], $section[1]);
            }
            $data[] = [
                'x' => $x,
                'y' => $y,
            ];
        }

        $this->options = [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            // 'subtitle' => [
            //     'text' => $this->description,

            // ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'series' => [
                [
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                // 'type' => 'datetime',
                'type' => $this->widget->parameters['interval'] === 'day' ? 'datetime' : 'category',
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
            'algorithm' => Select::make('algorithm')
                ->label(__('Algorithm'))
                ->options(WidgetPerformanceAlgorithm::class)
                ->required(),
        ];
    }
}
