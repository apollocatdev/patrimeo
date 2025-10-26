<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UpdateTransactionPeriodicity: string implements HasLabel
{
    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case QUARTER = 'quarter';
    case YEAR = 'year';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MINUTE => __('Minute(s)'),
            self::HOUR => __('Hour(s)'),
            self::DAY => __('Day(s)'),
            self::WEEK => __('Week(s)'),
            self::MONTH => __('Month(s)'),
            self::QUARTER => __('Quarter(s)'),
            self::YEAR => __('Year(s)'),
        };
    }

    public static function dropdown()
    {
        $dropdown = [];
        foreach (self::cases() as $case) {
            $dropdown[] = ['label' => $case->getLabel(), 'value' => $case->value];
        }
        return $dropdown;
    }
}
