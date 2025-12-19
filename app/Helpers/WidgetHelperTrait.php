<?php


namespace App\Helpers;

use App\Models\Asset;
use App\Models\Valuation;
use App\Data\WidgetFilterData;

trait WidgetHelperTrait
{

    protected function getQuery(string $entity)
    {
        switch ($entity) {
            case 'assets':
                return Asset::query();
            case 'valuations':
                return Valuation::query();
            default:
                return null;
        }
    }

    protected function applyOperation($query, ?string $column, string $operation)
    {
        if ($operation === 'count') {
            return $query->count();
        }
        if ($operation === 'sum') {
            return round($query->sum($column), 2);
        }
        if ($operation === 'avg') {
            return round($query->avg($column), 2);
        }
        if ($operation === 'min') {
            return round($query->min($column), 2);
        }
        if ($operation === 'max') {
            return round($query->max($column), 2);
        }
        return $query;
    }

    protected function applyFilters($query, ?array $filters)
    {
        if ($filters === null) {
            return $query;
        }
        foreach ($filters as $filter) {
            $query = $query->where($filter['field'], $filter['operator'], $filter['value']);
        }
        return $query;
    }
}
