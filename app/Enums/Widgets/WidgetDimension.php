<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetDimension: string implements HasLabel
{
    case ASSET_CLASS = 'class';
    case ENVELOP = 'envelop';
    case ENVELOP_TYPE = 'envelop_type';
    case CURRENCY = 'currency';
    case TAXONOMY = 'taxonomy';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSET_CLASS => __('Class'),
            self::ENVELOP => __('Envelop'),
            self::ENVELOP_TYPE => __('Envelop Type'),
            self::CURRENCY => __('Currency'),
            self::TAXONOMY => __('Taxonomy'),
        };
    }
}
