<?php


namespace App\Charts\Stats;

use App\Enums\Widgets\WidgetType;
use App\Charts\AbstractStat;
use App\Helpers\PortfolioCompute;
use App\Enums\Widgets\WidgetTimePeriod;
use App\Enums\Widgets\WidgetPerformanceAlgorithm;
use Filament\Forms\Components\Select;

class PortfolioPerformance extends AbstractStat
{
    public static WidgetType $type = WidgetType::STAT_PORTFOLIO_PERFORMANCE;

    protected function compute()
    {
        $algorithm = $this->widget->parameters['algorithm'];
        $date1 = $this->sinceToDate();
        $date2 = now();
        if ($algorithm === 'TWR') {
            $performance = (new PortfolioCompute($this->widget->filters))->getTWRPerformance($date1, $date2);
        }
        if ($algorithm === 'MWR') {
            $performance = (new PortfolioCompute($this->widget->filters))->getMWRPerformance($date1, $date2);
        }
        $this->descriptionIcon = $performance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $this->value = $performance . ' %';
    }


    public static function form(): array
    {
        return [
            Select::make('since')
                ->label(__('Since'))
                ->options(WidgetTimePeriod::class)
                ->required(),
            Select::make('algorithm')
                ->label(__('Algorithm'))
                ->options(WidgetPerformanceAlgorithm::class)
                ->required(),
        ];
    }
}
