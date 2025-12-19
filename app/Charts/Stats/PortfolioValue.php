<?php


namespace App\Charts\Stats;

use App\Enums\Widgets\WidgetType;
use App\Helpers\Currency;
use App\Charts\AbstractStat;
use App\Helpers\PortfolioCompute;

class PortfolioValue extends AbstractStat
{
    public static WidgetType $type = WidgetType::STAT_PORTFOLIO_VALUE;

    protected function compute()
    {
        $this->value = Currency::toCurrency((new PortfolioCompute($this->widget->filters))->portfolioValue(null));
    }

    public static function form(): array
    {
        return [];
    }
}
