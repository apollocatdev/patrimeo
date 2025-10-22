<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\Cotation;
use App\Models\Currency;
use App\Helpers\IntegrityHelper;
use App\Helpers\Logs\LogCotations;
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
    protected Collection $cotations;
    protected ?Currency $defaultCurrency;
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        // Log job start
        LogCotations::info("Starting CheckIntegrity job for user {$this->userId}");

        // Load all data once with eager loading
        $this->loadData();

        $integrityResults = [
            'checks' => [
                'assets_without_cotation' => $this->checkAssetsWithoutCotation(),
                'assets_without_envelop' => $this->checkAssetsWithoutEnvelop(),
                'assets_without_quantity' => $this->checkAssetsWithoutQuantity(),
                'assets_without_class' => $this->checkAssetsWithoutClass(),
                'assets_without_update_method' => $this->checkAssetsWithoutUpdateMethod(),
                'cotations_without_currency' => $this->checkCotationsWithoutCurrency(),
                'cotations_without_update_method' => $this->checkCotationsWithoutUpdateMethod(),
                'cotations_without_assets' => $this->checkCotationsWithoutAssets(),
                'currency_conversions' => $this->checkRequiredCurrencyConversions(),
            ]
        ];

        // Store results in cache
        IntegrityHelper::store($this->userId, $integrityResults);

        // Log problems found
        foreach ($integrityResults['checks'] as $checkName => $result) {
            if ($result['count'] > 0) {
                LogCotations::debug("Integrity check '{$checkName}': {$result['count']} problems found", [
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
                'cotation',
                'envelop',
                'class',
                'cotation.currency'
            ])
            ->get();

        // Load cotations with currency in one query
        $this->cotations = Cotation::where('user_id', $this->userId)
            ->with('currency')
            ->get();

        // Get default currency once
        $this->defaultCurrency = Currency::getDefault();
    }

    protected function checkAssetsWithoutCotation(): array
    {
        $assetsWithoutCotation = $this->assets->filter(function ($asset) {
            return $asset->cotation === null;
        });

        return [
            'level' => 'alert',
            'count' => $assetsWithoutCotation->count(),
            'items' => $assetsWithoutCotation->pluck('name')->toArray()
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

    protected function checkCotationsWithoutCurrency(): array
    {
        $cotationsWithoutCurrency = $this->cotations->filter(function ($cotation) {
            return $cotation->currency === null;
        });

        return [
            'level' => 'alert',
            'count' => $cotationsWithoutCurrency->count(),
            'items' => $cotationsWithoutCurrency->pluck('name')->toArray()
        ];
    }

    protected function checkCotationsWithoutUpdateMethod(): array
    {
        $cotationsWithoutUpdateMethod = $this->cotations->filter(function ($cotation) {
            return $cotation->update_method === null;
        });

        return [
            'level' => 'alert',
            'count' => $cotationsWithoutUpdateMethod->count(),
            'items' => $cotationsWithoutUpdateMethod->pluck('name')->toArray()
        ];
    }

    protected function checkCotationsWithoutAssets(): array
    {
        // Get all cotation IDs that are used by assets
        $usedCotationIds = $this->assets->pluck('cotation_id')->filter()->unique();

        // Get all currency symbols for validation
        $currencySymbols = Currency::pluck('symbol')->toArray();

        // Get cotations that have no assets, excluding currency conversion cotations
        $cotationsWithoutAssets = $this->cotations->filter(function ($cotation) use ($usedCotationIds, $currencySymbols) {
            // Skip if cotation is used by assets
            if ($usedCotationIds->contains($cotation->id)) {
                return false;
            }

            // Skip if it's a currency conversion cotation
            // Check if it's a 6-letter combination of two existing currencies (e.g., EURUSD)
            if (strlen($cotation->name) === 6) {
                $firstCurrency = substr($cotation->name, 0, 3);
                $secondCurrency = substr($cotation->name, 3, 3);

                if (in_array($firstCurrency, $currencySymbols) && in_array($secondCurrency, $currencySymbols)) {
                    return false; // It's a valid currency conversion cotation
                }
            }

            // Skip if it's an individual currency cotation (e.g., EUR, USD, GBP)
            if (in_array($cotation->name, $currencySymbols)) {
                return false; // It's a valid individual currency cotation
            }

            return true;
        });

        return [
            'level' => 'warning',
            'count' => $cotationsWithoutAssets->count(),
            'items' => $cotationsWithoutAssets->pluck('name')->toArray()
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

        // Get cotations with different currency than default
        $cotationsWithDifferentCurrency = $this->cotations->filter(function ($cotation) {
            return $cotation->currency && $cotation->currency->symbol !== $this->defaultCurrency->symbol;
        });

        if ($cotationsWithDifferentCurrency->isEmpty()) {
            return [
                'level' => 'success',
                'count' => 0,
                'items' => []
            ];
        }

        // Check if required conversion cotations exist
        $missingConversions = [];
        foreach ($cotationsWithDifferentCurrency as $cotation) {
            $conversionName = $cotation->currency->symbol . $this->defaultCurrency->symbol;

            $conversionExists = $this->cotations->contains('name', $conversionName);

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
