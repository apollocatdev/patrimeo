<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\Valuation;
use App\Models\Currency;
use App\Helpers\IntegrityHelper;
use App\Helpers\Logs\LogValuations;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable as BusQueueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;

class CheckIntegrity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, BusQueueable;

    protected Collection $assets;
    protected Collection $valuations;
    protected ?Currency $defaultCurrency;
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        // Log job start
        LogValuations::info("Starting CheckIntegrity job for user {$this->userId}");

        // Load all data once with eager loading
        $this->loadData();

        $integrityResults = [
            'checks' => [
                'assets_without_valuation' => $this->checkAssetsWithoutValuation(),
                'assets_without_envelop' => $this->checkAssetsWithoutEnvelop(),
                'assets_without_quantity' => $this->checkAssetsWithoutQuantity(),
                'assets_without_class' => $this->checkAssetsWithoutClass(),
                'assets_without_update_method' => $this->checkAssetsWithoutUpdateMethod(),
                'valuations_without_currency' => $this->checkValuationsWithoutCurrency(),
                'valuations_without_update_method' => $this->checkValuationsWithoutUpdateMethod(),
                'valuations_without_assets' => $this->checkValuationsWithoutAssets(),
                'currency_conversions' => $this->checkRequiredCurrencyConversions(),
            ]
        ];

        // Store results in cache
        IntegrityHelper::store($this->userId, $integrityResults);

        // Log problems found
        foreach ($integrityResults['checks'] as $checkName => $result) {
            if ($result['count'] > 0) {
                LogValuations::debug("Integrity check '{$checkName}': {$result['count']} problems found", [
                    'level' => $result['level'],
                    'items' => $result['items']
                ]);
            }
        }
    }

    protected function loadData(): void
    {
        // Load assets with all related data in one query
        $this->assets = Asset::where('user_id', $this->userId)
            ->with([
                'valuation',
                'envelop',
                'class',
                'valuation.currency'
            ])
            ->get();

        // Load valuations with currency in one query
        $this->valuations = Valuation::where('user_id', $this->userId)
            ->with('currency')
            ->get();

        // Get default currency once
        $this->defaultCurrency = Currency::getDefault();
    }

    protected function checkAssetsWithoutValuation(): array
    {
        $assetsWithoutValuation = $this->assets->filter(function ($asset) {
            return $asset->valuation === null;
        });

        return [
            'level' => 'alert',
            'count' => $assetsWithoutValuation->count(),
            'items' => $assetsWithoutValuation->pluck('name')->toArray()
        ];
    }

    protected function checkAssetsWithoutEnvelop(): array
    {
        $assetsWithoutEnvelop = $this->assets->filter(function ($asset) {
            return $asset->envelop === null;
        });

        return [
            'level' => 'warning',
            'count' => $assetsWithoutEnvelop->count(),
            'items' => $assetsWithoutEnvelop->pluck('name')->toArray()
        ];
    }

    protected function checkAssetsWithoutQuantity(): array
    {
        $assetsWithoutQuantity = $this->assets->filter(function ($asset) {
            return $asset->quantity === null || $asset->quantity == 0;
        });

        return [
            'level' => 'alert',
            'count' => $assetsWithoutQuantity->count(),
            'items' => $assetsWithoutQuantity->pluck('name')->toArray()
        ];
    }

    protected function checkAssetsWithoutClass(): array
    {
        $assetsWithoutClass = $this->assets->filter(function ($asset) {
            return $asset->class === null;
        });

        return [
            'level' => 'warning',
            'count' => $assetsWithoutClass->count(),
            'items' => $assetsWithoutClass->pluck('name')->toArray()
        ];
    }

    protected function checkAssetsWithoutUpdateMethod(): array
    {
        $assetsWithoutUpdateMethod = $this->assets->filter(function ($asset) {
            return $asset->update_method === null;
        });

        return [
            'level' => 'alert',
            'count' => $assetsWithoutUpdateMethod->count(),
            'items' => $assetsWithoutUpdateMethod->pluck('name')->toArray()
        ];
    }

    protected function checkValuationsWithoutCurrency(): array
    {
        $valuationsWithoutCurrency = $this->valuations->filter(function ($valuation) {
            return $valuation->currency === null;
        });

        return [
            'level' => 'alert',
            'count' => $valuationsWithoutCurrency->count(),
            'items' => $valuationsWithoutCurrency->pluck('name')->toArray()
        ];
    }

    protected function checkValuationsWithoutUpdateMethod(): array
    {
        $valuationsWithoutUpdateMethod = $this->valuations->filter(function ($valuation) {
            return $valuation->update_method === null;
        });

        return [
            'level' => 'alert',
            'count' => $valuationsWithoutUpdateMethod->count(),
            'items' => $valuationsWithoutUpdateMethod->pluck('name')->toArray()
        ];
    }

    protected function checkValuationsWithoutAssets(): array
    {
        // Get all valuation IDs that are used by assets
        $usedValuationIds = $this->assets->pluck('valuation_id')->filter()->unique();

        // Get all currency symbols for validation
        $currencySymbols = Currency::pluck('symbol')->toArray();

        // Get valuations that have no assets, excluding currency conversion valuations
        $valuationsWithoutAssets = $this->valuations->filter(function ($valuation) use ($usedValuationIds, $currencySymbols) {
            // Skip if valuation is used by assets
            if ($usedValuationIds->contains($valuation->id)) {
                return false;
            }

            // Skip if it's a currency conversion valuation
            // Check if it's a 6-letter combination of two existing currencies (e.g., EURUSD)
            if (strlen($valuation->name) === 6) {
                $firstCurrency = substr($valuation->name, 0, 3);
                $secondCurrency = substr($valuation->name, 3, 3);

                if (in_array($firstCurrency, $currencySymbols) && in_array($secondCurrency, $currencySymbols)) {
                    return false; // It's a valid currency conversion valuation
                }
            }

            // Skip if it's an individual currency valuation (e.g., EUR, USD, GBP)
            if (in_array($valuation->name, $currencySymbols)) {
                return false; // It's a valid individual currency valuation
            }

            return true;
        });

        return [
            'level' => 'warning',
            'count' => $valuationsWithoutAssets->count(),
            'items' => $valuationsWithoutAssets->pluck('name')->toArray()
        ];
    }

    protected function checkRequiredCurrencyConversions(): array
    {
        if (!$this->defaultCurrency) {
            return [
                'level' => 'alert',
                'count' => 1,
                'items' => ['No default currency found']
            ];
        }

        // Get valuations with different currency than default
        $valuationsWithDifferentCurrency = $this->valuations->filter(function ($valuation) {
            return $valuation->currency && $valuation->currency->symbol !== $this->defaultCurrency->symbol;
        });

        if ($valuationsWithDifferentCurrency->isEmpty()) {
            return [
                'level' => 'success',
                'count' => 0,
                'items' => []
            ];
        }

        // Check if required conversion valuations exist
        $missingConversions = [];
        foreach ($valuationsWithDifferentCurrency as $valuation) {
            $conversionName = $valuation->currency->symbol . $this->defaultCurrency->symbol;

            $conversionExists = $this->valuations->contains('name', $conversionName);

            if (!$conversionExists && !in_array($conversionName, $missingConversions)) {
                $missingConversions[] = $conversionName;
            }
        }

        return [
            'level' => 'alert',
            'count' => count($missingConversions),
            'items' => $missingConversions
        ];
    }
}
