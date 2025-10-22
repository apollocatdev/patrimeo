<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetTimeInterval: string implements HasLabel
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAY => __('Per Day'),
            self::WEEK => __('Per Week'),
            self::MONTH => __('Per Month'),
            self::YEAR => __('Per Year'),
        };
    }
}
