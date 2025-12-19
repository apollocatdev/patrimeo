<?php

namespace App\Enums\Filters;

use Filament\Support\Contracts\HasLabel;

enum FilterRuleValuationType: string implements HasLabel
{

    case CURRENCY = 'currency';
    case UPDATE_METHOD = 'update_method';

    public function getLabel(): string
    {
        return match ($this) {
            self::CURRENCY => __('Currency'),
            self::UPDATE_METHOD => __('Update method'),
        };
    }
    public function isNumericRule(): bool
    {
        return in_array($this, []);
    }
}
