<?php

namespace App\Helpers\PortfolioCompute;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\Filter;
use App\Enums\TransactionType;
use Illuminate\Support\Carbon;
use App\Models\ValuationHistory;
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
        $transactions = Transaction::where('date', '<=', $this->date)->whereIn('source_id', $assetIds)->orWhereIn('destination_id', $assetIds)->get();

        foreach ($transactions as $transaction) {
            if ($transaction->type === TransactionType::Income) {
                $assets[$transaction->destination_id]->quantity -= $transaction->destination_quantity;
            }
            if ($transaction->type === TransactionType::Expense) {
                $assets[$transaction->source_id]->quantity += $transaction->source_quantity;
            }
            if ($transaction->type === TransactionType::Transfer) {
                $assets[$transaction->source_id]->quantity += $transaction->source_quantity;
                $assets[$transaction->destination_id]->quantity -= $transaction->destination_quantity;
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

        // $lastDate = ValuationHistory::where('date', '<=', $this->date)->orderBy('date', 'desc')->first()->date->format('Y-m-d');
        $beforeDateRecord = ValuationHistory::where('date', '<=', $this->date)->orderBy('date', 'desc')->first();
        if ($beforeDateRecord === null) {
            return $this;
        }
        $lastDate = $beforeDateRecord->date->format('Y-m-d');

        $valuations = ValuationHistory::where('date', $lastDate)->get()->keyBy('valuation_id');

        $this->assets->map(function (Asset $asset) use ($valuations) {
            if (isset($valuations[$asset->valuation_id])) {
                $asset->value = $asset->quantity * $valuations[$asset->valuation_id]->value_main_currency;
            } else {
                $asset->value = $asset->quantity * $asset->valuation->value_main_currency;
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
