<?php

namespace App\Data\Filters;

use App\Enums\Filters\FilterRuleValuationType;
use App\Enums\Filters\FilterRuleOperator;


class FilterRuleValuation
{
    public FilterRuleValuationType $type;
    public array $values;
    public ?FilterRuleOperator $operator;

    public function __construct(FilterRuleValuationType $type, array $values, ?FilterRuleOperator $operator = null)
    {
        $this->type = $type;
        $this->values = $values;
        $this->operator = $operator;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'values' => $this->values,
            'operator' => $this->operator,
        ];
    }
}
