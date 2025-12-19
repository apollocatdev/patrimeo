<?php

namespace App\Models;

use App\Data\Filters\Filters;
use App\Models\Scopes\UserScope;
use App\Enums\Filters\FilterEntity;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Filters\FilterLogicOperator;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Filters\FilterRuleAssetType;
use App\Enums\Filters\FilterRuleOperator;

#[ScopedBy([UserScope::class])]
class Filter extends Model
{
    protected $fillable = ['name', 'entity', 'filters', 'operation', 'user_id'];

    protected $casts = [
        'entity' => FilterEntity::class,
        'filters' => Filters::class,
        'parameters' => 'array',
        'operation' => FilterLogicOperator::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'filterables', 'filter_id', 'filterable_id')
            ->where('filterable_type', 'App\\Models\\Widget')
            ->using(WidgetFilter::class)
            ->withTimestamps();
    }

    public function applyToQuery(Builder $query): Builder
    {
        if ($this->entity !== FilterEntity::ASSETS) {
            return $query;
        }

        $query->where(function (Builder $query) {
            foreach ($this->filters->rules as $rule) {
                $this->applyRuleToQuery($query, $rule);
            }
        });

        return $query;
    }

    protected function applyRuleToQuery(Builder $query, $rule): void
    {
        $isOr = $this->operation === FilterLogicOperator::OR;
        $method = $isOr ? 'orWhere' : 'where';

        switch ($rule->type) {
            case FilterRuleAssetType::ASSET_CLASS:
                $query->{$method}(function (Builder $query) use ($rule) {
                    $query->whereHas('class', function (Builder $query) use ($rule) {
                        $query->whereIn('name', $rule->values);
                    });
                });
                break;

            case FilterRuleAssetType::ENVELOP:
                $query->{$method}(function (Builder $query) use ($rule) {
                    $query->whereHas('envelop', function (Builder $query) use ($rule) {
                        $query->whereIn('name', $rule->values);
                    });
                });
                break;

            case FilterRuleAssetType::TAXONOMY:
                $query->{$method}(function (Builder $query) use ($rule) {
                    $query->whereHas('tags', function (Builder $query) use ($rule) {
                        $query->whereIn('name', $rule->values);
                    });
                });
                break;

            case FilterRuleAssetType::UPDATE_METHOD:
                $query->{$method}(function (Builder $query) use ($rule) {
                    $query->whereIn('update_method', $rule->values);
                });
                break;

            case FilterRuleAssetType::VALUE:
                $this->applyNumericRule($query, $rule, 'value', $method);
                break;

            case FilterRuleAssetType::QUANTITY:
                $this->applyNumericRule($query, $rule, 'quantity', $method);
                break;
        }
    }

    protected function applyNumericRule(Builder $query, $rule, string $column, string $method): void
    {
        if (!$rule->operator) {
            return;
        }

        $operator = $rule->operator->value;
        $value = is_array($rule->values) ? $rule->values[0] : $rule->values;

        if (is_numeric($value)) {
            $query->{$method}($column, $operator, $value);
        }
    }
}
