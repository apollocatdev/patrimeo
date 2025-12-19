<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetPerformanceAlgorithm: string implements HasLabel
{
    case TWR = 'TWR';
    case MWR = 'MWR';

    public function getLabel(): string
    {
        return match ($this) {
            self::TWR => __('Time-Weighted Return'),
            self::MWR => __('Money-Weighted Return'),
        };
    }
}
