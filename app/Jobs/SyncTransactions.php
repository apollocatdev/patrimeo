<?php

namespace App\Jobs;

use Exception;
use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionUpdateMethod;
use App\Exceptions\TransactionsException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Settings\TransactionsSettings;
use App\Helpers\Logs\LogTransactions;

class SyncTransactions implements ShouldQueue
{
    use Queueable;

    protected array $rateLimiters;
    protected ?array $assetNames;
    protected ?int $userId;
    protected ?int $minHoursBetweenUpdates;

    /**
     * Create a new job instance.
     */
    public function __construct(?array $assetNames = null, ?int $userId = null)
    {
        $this->rateLimiters = [];
        $this->assetNames = $assetNames;
        $this->userId = $userId;
        $this->minHoursBetweenUpdates = TransactionsSettings::get()->minHoursBetweenUpdates;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Log job start
            $triggerType = $this->assetNames ? 'manual' : 'automatic';
            $userInfo = $this->userId ? " for user {$this->userId}" : '';
            LogTransactions::info("Starting transaction update ({$triggerType}){$userInfo}");

            // Build base query
            $query = Asset::query();

            // Filter by user if specified
            if ($this->userId !== null) {
                $query->where('user_id', $this->userId);
            }

            // Filter by asset names if specified
            if ($this->assetNames !== null) {
                $query->whereIn('name', $this->assetNames);
            }

            // Exclude assets that were updated recently based on min_hours_between_updates setting
            if ($this->minHoursBetweenUpdates > 0) {
                $query->where(function ($q) {
                    $q->whereNull('last_update')
                        ->orWhere('last_update', '<', now()->subHours($this->minHoursBetweenUpdates));
                });
            }

            // Get all assets to process
            $assets = $query->get();

            foreach ($assets as $asset) {
                $this->sleepIfNeeded($asset);
                $this->updateAssetTransactions($asset);
                $this->rateLimiters[$asset->update_method->getRateLimiterKey()] = $asset->updated_at;
            }
        } catch (Exception $e) {
            LogTransactions::error("Transaction update failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function sleepIfNeeded(Asset $asset): void
    {
        $rateLimiterKey = $asset->update_method->getRateLimiterKey();

        if (!array_key_exists($rateLimiterKey, $this->rateLimiters)) {
            return;
        }

        $lastUpdate = $this->rateLimiters[$rateLimiterKey];
        if ($lastUpdate > now()->subSeconds(config('custom.rate_limiters.' . $rateLimiterKey))) {
            $sleepTime = array_key_exists($rateLimiterKey, config('custom.rate_limiters'))
                ? config('custom.rate_limiters')[$rateLimiterKey]
                : config('custom.rate_limiters.default');

            LogTransactions::info("Rate limiter activated for {$rateLimiterKey}, sleeping {$sleepTime} seconds");
            sleep($sleepTime);
        }
    }

    public function updateAssetTransactions(Asset $asset): Asset
    {
        $class = $asset->update_method->getServiceClass();

        if ($class !== null) {
            try {
                (new $class($asset))->saveTransactions();

                $asset->computeQuantity();

                LogTransactions::info("Updated transactions for asset {$asset->name}");
            } catch (TransactionsException $e) {
                // Re-throw TransactionsException as-is since it already has all the details
                throw $e;
            } catch (Exception $e) {
                // Wrap generic exceptions in TransactionsException
                throw new TransactionsException(
                    $asset,
                    $e->getMessage(),
                    null,
                    null,
                    ['type' => 'transaction_update']
                );
            }
        }

        return $asset;
    }
}
