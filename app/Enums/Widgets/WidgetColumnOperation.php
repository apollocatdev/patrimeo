<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetColumnOperation: string implements HasLabel
{
    case COUNT = 'count';
    case SUM = 'sum';
    case AVG = 'avg';
    case MIN = 'min';
    case MAX = 'max';

    public function getLabel(): string
    {
        return match ($this) {
            self::COUNT => __('Count'),
            self::SUM => __('Sum'),
            self::AVG => __('Average'),
            self::MIN => __('Minimum'),
            self::MAX => __('Maximum'),
        };
    }
}
