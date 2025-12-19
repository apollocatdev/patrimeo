<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaxonomyTypes: string implements HasLabel
{
    case ASSETS = 'assets';
    case TRANSACTIONS = 'transactions';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSETS => __('Assets'),
            self::TRANSACTIONS => __('Transactions'),
        };
    }
}
