<?php

namespace App\Data\Filters;

use App\Enums\Filters\FilterRuleCotationType;
use App\Enums\Filters\FilterRuleOperator;


class FilterRuleCotation
{
    public FilterRuleCotationType $type;
    public array $values;
    public ?FilterRuleOperator $operator;

    public function __construct(FilterRuleCotationType $type, array $values, ?FilterRuleOperator $operator = null)
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
