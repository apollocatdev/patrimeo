<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetEntity: string implements HasLabel
{
    case COTATIONS = 'cotations';
    case ASSETS = 'assets';


    public function getLabel(): string
    {
        return match ($this) {
            self::VALUATIONS => __('Valuations'),
            self::ASSETS => __('Assets'),
        };
    }
}
