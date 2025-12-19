<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetTimePeriod: string implements HasLabel
{
    case YTD = 'YTD';
    case MTD = 'MTD';
    case ONE_WEEK = '1W';
    case SIX_MONTHS = '6M';
    case ONE_YEAR = '1Y';
    case BEGINNING = 'Beginning';

    public function getLabel(): string
    {
        return match ($this) {
            self::YTD => __('Year to Date'),
            self::MTD => __('Month to Date'),
            self::ONE_WEEK => __('Last Week'),
            self::SIX_MONTHS => __('Last 6 Months'),
            self::ONE_YEAR => __('Last Year'),
            self::BEGINNING => __('Since Beginning'),
        };
    }
}
