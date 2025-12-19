<?php

namespace App\Enums\Filters;

use Filament\Support\Contracts\HasLabel;

enum FilterRuleOperator: string implements HasLabel
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUALS = '>=';
    case LESS_THAN_OR_EQUALS = '<=';

    public function getLabel(): string
    {
        return match ($this) {
            self::EQUALS => '=',
            self::NOT_EQUALS => '!=',
            self::GREATER_THAN => '>',
            self::LESS_THAN => '<',
            self::GREATER_THAN_OR_EQUALS => '>=',
            self::LESS_THAN_OR_EQUALS => '<=',
        };
    }

    public function isNumeric(): bool
    {
        return in_array($this, [
            self::GREATER_THAN,
            self::LESS_THAN,
            self::GREATER_THAN_OR_EQUALS,
            self::LESS_THAN_OR_EQUALS,
        ]);
    }
}
