<?php


namespace App\Charts\Stats;

use App\Enums\Widgets\WidgetType;
use App\Helpers\Currency;
use App\Charts\AbstractStat;
use App\Helpers\PortfolioCompute;
use App\Enums\Widgets\WidgetTimePeriod;
use Filament\Forms\Components\Select;

class PortfolioGain extends AbstractStat
{
    public static WidgetType $type = WidgetType::STAT_PORTFOLIO_GAIN;

    protected function compute()
    {

        $date = $this->sinceToDate();
        $actualValue = (new PortfolioCompute($this->widget->filters))->portfolioValue(null);
        $olderValue = (new PortfolioCompute($this->widget->filters))->portfolioValue($date);

        $difference = $actualValue - $olderValue;
        $percentage = round(($difference / $olderValue) * 100, 2);

        $this->value = Currency::toCurrency($difference);

        if ($difference < 0) {
            $this->description = $percentage . '%';
            $this->descriptionIcon = 'heroicon-m-arrow-trending-down';
            $this->color = 'danger';
        } else {
            $this->description = '+' . $percentage . '%';
            $this->descriptionIcon = 'heroicon-m-arrow-trending-up';
            $this->color = 'success';
        }
    }



    public static function form(): array
    {
        return [
            Select::make('since')
                ->label(__('Since'))
                ->options(WidgetTimePeriod::class)
                ->required(),
        ];
    }
}
