<?php

namespace App\Enums\Filters;

use Filament\Support\Contracts\HasLabel;

enum FilterLogicOperator: string implements HasLabel
{
    case AND = 'and';
    case OR = 'or';

    public function getLabel(): string
    {
        return match ($this) {
            self::AND => 'AND',
            self::OR => 'OR',
        };
    }
}
