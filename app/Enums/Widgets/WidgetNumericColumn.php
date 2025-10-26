<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetNumericColumn: string implements HasLabel
{
    case QUANTITY = 'quantity';
    case VALUE = 'value';
    case VALUE_MAIN_CURRENCY = 'value_main_currency';

    public function getLabel(): string
    {
        return match ($this) {
            self::QUANTITY => __('Quantity'),
            self::VALUE => __('Value'),
            self::VALUE_MAIN_CURRENCY => __('Value in Main Currency'),
        };
    }

    public function getEntity(): string
    {
        return match ($this) {
            self::QUANTITY, self::VALUE => 'assets',
            self::VALUE_MAIN_CURRENCY => 'valuations',
        };
    }
}
