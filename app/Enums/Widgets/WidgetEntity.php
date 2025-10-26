<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetEntity: string implements HasLabel
{
    case VALUATIONS = 'valuations';
    case ASSETS = 'assets';


    public function getLabel(): string
    {
        return match ($this) {
            self::VALUATIONS => __('Valuations'),
            self::ASSETS => __('Assets'),
        };
    }
}
