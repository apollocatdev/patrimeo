<?php

namespace App\Jobs;

use Exception;
use App\Models\Asset;
use App\Models\Valuation;
use App\Models\Currency;
use App\Models\ValuationHistory;
use App\Exceptions\ValuationException;
use App\Helpers\Logs\LogValuations;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAllValues implements ShouldQueue
{
    use Queueable;

    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Set user context for logging
        LogValuations::setCurrentUserId($this->userId);

        // Log job start
        LogValuations::info("Starting UpdateAllValues job");

        $valuations = Valuation::all();
        foreach ($valuations as $valuation) {
            if ($valuation->currency->main) {
                $valuation->value_main_currency = $valuation->value;
                $valuation->saveQuietly();
            } else {
                if ($valuation->value === null) {
                    continue;
                }
                try {
                    $mainCurrency = Currency::where('main', true)->first();
                    $pair = $valuation->currency->symbol . $mainCurrency->symbol;
                    $valuationMainCurrency = Valuation::where('name', $pair)->first();
                    if ($valuationMainCurrency !== null) {
                        $valuation->value_main_currency = $valuation->value * $valuationMainCurrency->value;
                        $valuation->saveQuietly();

                        $valuationHistory = ValuationHistory::where('valuation_id', $valuation->id)->whereDate('date', today())->first();
                        if ($valuationHistory === null) {
                            ValuationHistory::create([
                                'valuation_id' => $valuation->id,
                                'date' => today(),
                                'value' => $valuation->value,
                                'value_main_currency' => $valuation->value_main_currency,
                                'user_id' => $valuation->user_id,
                            ]);
                        } else {
                            $valuationHistory->update([
                                'value' => $valuation->value,
                                'value_main_currency' => $valuation->value_main_currency,
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    throw new ValuationException($valuation, $e->getMessage());
                }
            }
        }

        $assets = Asset::all();
        foreach ($assets as $asset) {
            $oldValue = $asset->value;
            $asset->value = $asset->quantity * $asset->valuation->value_main_currency;
            $asset->saveQuietly();

            // Log asset value calculation
            LogValuations::debug("Asset value calculated: {$asset->name} = {$asset->value} (was: {$oldValue})");
        }
    }
}
