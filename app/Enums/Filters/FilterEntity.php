<?php

namespace App\Enums\Filters;

use Filament\Support\Contracts\HasLabel;

enum FilterEntity: string implements HasLabel
{
    case ASSETS = 'assets';
    case VALUATIONS = 'valuations';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSETS => __('Assets'),
            self::VALUATIONS => __('Valuations'),
        };
    }
}
