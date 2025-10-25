<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Widget;
use App\Models\Valuation;
use App\Enums\Widgets\WidgetType;
use App\Models\WidgetStat;
use App\Helpers\WidgetHelperTrait;
use App\Models\WidgetStatOverview;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DynamicStat extends BaseWidget
{
    // use WidgetHelperTrait;

    public array $stats;

    // protected function getPollingInterval(): ?string
    // {
    //     return $this->widget->polling === null ? null : $this->widget->polling . 's';
    // }

    protected function getStats(): array
    {
        $stats = [];
        foreach ($this->stats as $stat) {
            $stats[] = Stat::make($stat['label'], $stat['value'])
                ->description($stat['description'])
                ->color($stat['color'])
                ->icon($stat['descriptionIcon']);
        }

        return $stats;
    }
}
