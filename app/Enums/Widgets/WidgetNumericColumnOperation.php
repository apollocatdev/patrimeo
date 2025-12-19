<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetNumericColumnOperation: string implements HasLabel
{
    case SUM = 'sum';
    case AVG = 'avg';
    case MIN = 'min';
    case MAX = 'max';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUM => __('Sum'),
            self::AVG => __('Average'),
            self::MIN => __('Minimum'),
            self::MAX => __('Maximum'),
        };
    }
}
