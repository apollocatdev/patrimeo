<?php


namespace App\Charts\Stats;

use App\Charts\AbstractStat;
use App\Enums\Widgets\WidgetType;
use App\Helpers\PortfolioCompute;
use App\Enums\Widgets\WidgetEntity;
use Filament\Forms\Components\Select;

class NumberOfEntities extends AbstractStat
{
    public static WidgetType $type = WidgetType::STAT_NUMBER_OF_ENTITIES;

    protected function compute()
    {
        if ($this->widget->parameters['entity'] === 'assets') {
            $this->value = (new PortfolioCompute($this->widget->filters))->portfolioCountAssets(null);
        }
    }

    public static function form(): array
    {
        return [
            Select::make('entity')
                ->label(__('Entity'))
                ->options(WidgetEntity::class)
                ->required(),
        ];
    }
}
