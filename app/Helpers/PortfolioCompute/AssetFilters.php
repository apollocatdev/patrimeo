<?php

namespace App\Helpers\PortfolioCompute;

use App\Models\Asset;
use App\Models\Filter;
use App\Enums\Filters\FilterEntity;
use App\Data\Filters\FilterRuleAsset;
use App\Enums\Filters\FilterRuleAssetType;

class AssetFilters
{
    protected Asset $asset;
    protected Filter $filter;

    public function __construct(Asset $asset, Filter $filter)
    {
        $this->asset = $asset;
        $this->filter = $filter;
    }

    public function check()
    {
        $result = false;
        foreach ($this->filter->filters as $filter) {
            // $result = $this->checkOne($filter);
            if ($this->filter->entity === FilterEntity::ASSETS) {
                $result = $this->checkFilterRuleAsset($filter);
            }
            if ($this->filter->operation === 'or') {
                if ($result) {
                    $result = true;
                    break;
                }
            } else {
                if (!$result) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    protected function checkFilterRuleAsset(FilterRuleAsset $filter)
    {
        if ($filter->type === FilterRuleAssetType::ASSET_CLASS) {
            return in_array($this->asset->class->name, $filter->values);
        }
        if ($filter->type === FilterRuleAssetType::ENVELOP) {
            return in_array($this->asset->envelop->name, $filter->values);
        }
        return false;
    }
}
