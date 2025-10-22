<?php

namespace App\Jobs;

use Exception;
use App\Models\Asset;
use App\Models\Cotation;
use App\Models\Currency;
use App\Models\CotationHistory;
use App\Exceptions\CotationException;
use App\Helpers\Logs\LogCotations;
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
        LogCotations::setCurrentUserId($this->userId);

        // Log job start
        LogCotations::info("Starting UpdateAllValues job");

        $cotations = Cotation::all();
        foreach ($cotations as $cotation) {
            if ($cotation->currency->main) {
                $cotation->value_main_currency = $cotation->value;
                $cotation->saveQuietly();
            } else {
                if ($cotation->value === null) {
                    continue;
                }
                try {
                    $mainCurrency = Currency::where('main', true)->first();
                    $pair = $cotation->currency->symbol . $mainCurrency->symbol;
                    $cotationMainCurrency = Cotation::where('name', $pair)->first();
                    if ($cotationMainCurrency !== null) {
                        $cotation->value_main_currency = $cotation->value * $cotationMainCurrency->value;
                        $cotation->saveQuietly();

                        $cotationHistory = CotationHistory::where('cotation_id', $cotation->id)->whereDate('date', today())->first();
                        if ($cotationHistory === null) {
                            CotationHistory::create([
                                'cotation_id' => $cotation->id,
                                'date' => today(),
                                'value' => $cotation->value,
                                'value_main_currency' => $cotation->value_main_currency,
                                'user_id' => $cotation->user_id,
                            ]);
                        } else {
                            $cotationHistory->update([
                                'value' => $cotation->value,
                                'value_main_currency' => $cotation->value_main_currency,
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    throw new CotationException($cotation, $e->getMessage());
                }
            }
        }

        $assets = Asset::all();
        foreach ($assets as $asset) {
            $oldValue = $asset->value;
            $asset->value = $asset->quantity * $asset->cotation->value_main_currency;
            $asset->saveQuietly();

            // Log asset value calculation
            LogCotations::debug("Asset value calculated: {$asset->name} = {$asset->value} (was: {$oldValue})");
        }
    }
}
