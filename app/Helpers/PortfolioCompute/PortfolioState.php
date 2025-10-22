<?php

namespace App\Helpers\PortfolioCompute;

use App\Models\Asset;
use App\Models\Transfer;
use App\Models\Filter;
use App\Enums\TransferType;
use Illuminate\Support\Carbon;
use App\Models\CotationHistory;
use Illuminate\Support\Collection;

class PortfolioState
{
    protected Collection $assets;
    protected ?Carbon $date = null;
    protected Collection $filters;

    public function __construct(?Carbon $date, ?Collection $filters = null)
    {
        $this->assets = Asset::all();
        $this->date = $date;
        $this->filters = $filters ?? collect();
        $this->setQuantities()->setValues()->applyFilters();
    }

    public function setQuantities(): self
    {
        if ($this->date === null) {
            return $this;
        }
        $assetIds = $this->assets->pluck('id');
        $assets = $this->assets->keyBy('id');
        $transfers = Transfer::where('date', '<=', $this->date)->whereIn('source_id', $assetIds)->orWhereIn('destination_id', $assetIds)->get();

        foreach ($transfers as $transfer) {
            if ($transfer->type === TransferType::Income) {
                $assets[$transfer->destination_id]->quantity -= $transfer->destination_quantity;
            }
            if ($transfer->type === TransferType::Expense) {
                $assets[$transfer->source_id]->quantity += $transfer->source_quantity;
            }
            if ($transfer->type === TransferType::Transfer) {
                $assets[$transfer->source_id]->quantity += $transfer->source_quantity;
                $assets[$transfer->destination_id]->quantity -= $transfer->destination_quantity;
            }
        }
        $this->assets = $assets->values();
        return $this;
    }

    public function setValues(): self
    {
        if ($this->date === null) {
            return $this;
        }

        // $lastDate = CotationHistory::where('date', '<=', $this->date)->orderBy('date', 'desc')->first()->date->format('Y-m-d');
        $beforeDateRecord = CotationHistory::where('date', '<=', $this->date)->orderBy('date', 'desc')->first();
        if ($beforeDateRecord === null) {
            return $this;
        }
        $lastDate = $beforeDateRecord->date->format('Y-m-d');

        $cotations = CotationHistory::where('date', $lastDate)->get()->keyBy('cotation_id');

        $this->assets->map(function (Asset $asset) use ($cotations) {
            if (isset($cotations[$asset->cotation_id])) {
                $asset->value = $asset->quantity * $cotations[$asset->cotation_id]->value_main_currency;
            } else {
                $asset->value = $asset->quantity * $asset->cotation->value_main_currency;
            }
            return $asset;
        });
        return $this;
    }

    public function applyFilters(): self
    {
        $filteredAssets = collect([]);

        foreach ($this->assets as $asset) {
            $allFiltersPassed = true;
            foreach ($this->filters as $filter) {
                // Extract the filter rules from each Filter model
                foreach ($filter->filters->rules as $rule) {
                    $af = new AssetFilters($asset, $rule);
                    if (! $af->check()) {
                        $allFiltersPassed = false;
                        break 2; // Break out of both loops
                    }
                }
            }
            if ($allFiltersPassed) {
                $filteredAssets->push($asset);
            }
        }
        $this->assets = $filteredAssets;
        return $this;
    }

    public function value(): float
    {
        return $this->assets->sum('value');
    }

    public function count(): int
    {
        return $this->assets->count();
    }

    public function assets(): Collection
    {
        return $this->assets;
    }
}
