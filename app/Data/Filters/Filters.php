<?php

namespace App\Data\Filters;

use App\Models\Filter;
use Illuminate\Support\Collection;
use App\Enums\Filters\FilterEntity;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Filters\FilterRuleAssetType;
use App\Enums\Filters\FilterRuleValuationType;
use App\Data\Filters\FilterRuleAsset;
use App\Data\Filters\FilterRuleValuation;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class Filters implements Castable
{

    public function __construct(public Collection $rules) {}


    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): Filters
            {
                $result = collect();
                $filters = json_decode($value, true);

                foreach ($filters as $filter) {
                    $result->push(Filters::createFilterRule($filter, $model->entity));
                }

                return new Filters($result);
            }

            public function set(Model $model, string $key, $value, array $attributes): array
            {
                if ($value instanceof Filters) {
                    $filters = $value->rules->map(function ($filter) {
                        return $filter->toArray();
                    })->toArray();

                    return [$key => json_encode($filters)];
                }

                return [$key => $value];
            }
        };
    }

    public function toArray(): array
    {
        return $this->rules->map(function ($rule) {
            return [
                'type' => $rule->type->value,
                'values' => is_array($rule->values) ? implode(', ', $rule->values) : $rule->values,
                'operator' => $rule->operator ?? null,
            ];
        })->toArray();
    }



    public static function fromFormArray(array $data, FilterEntity $entity): self
    {
        $rules = collect($data)->map(function ($item) use ($entity) {
            $values = array_filter(array_map('trim', explode(',', $item['values'])));

            return self::createFilterRule([
                'type' => $item['type'],
                'values' => $values,
                'operator' => $item['operator'],
            ], $entity);
        })->filter();

        return new self($rules);
    }

    public static function createFilterRule(array $filter, FilterEntity $entity): FilterRuleAsset|FilterRuleValuation
    {
        if ($entity === FilterEntity::ASSETS) {
            return new FilterRuleAsset(
                FilterRuleAssetType::from($filter['type']),
                $filter['values'],
                $filter['operator'] ?? null
            );
        }

        if ($entity === FilterEntity::VALUATIONS) {
            return new FilterRuleValuation(
                FilterRuleValuationType::from($filter['type']),
                $filter['values'],
                $filter['operator'] ?? null
            );
        }

        throw new \InvalidArgumentException("Unknown entity type: {$entity->value}");
    }
}
