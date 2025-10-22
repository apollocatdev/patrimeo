<?php

namespace App\Jobs;

use Exception;
use App\Models\Asset;
use App\Models\Transfer;
use App\Enums\TransferUpdateMethod;
use App\Exceptions\TransfersException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Settings\AssetTransferSettings;
use App\Helpers\Logs\LogTransfers;

class SyncTransfers implements ShouldQueue
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
        $this->minHoursBetweenUpdates = AssetTransferSettings::get()->minHoursBetweenUpdates;
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
            LogTransfers::info("Starting transfer update ({$triggerType}){$userInfo}");

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
                $this->updateAssetTransfers($asset);
                $this->rateLimiters[$asset->getRateLimiterKey()] = $asset->updated_at;
            }
        } catch (Exception $e) {
            LogTransfers::error("Transfer update failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function sleepIfNeeded(Asset $asset): void
    {
        $rateLimiterKey = $asset->getRateLimiterKey();

        if (!array_key_exists($rateLimiterKey, $this->rateLimiters)) {
            return;
        }

        $lastUpdate = $this->rateLimiters[$rateLimiterKey];
        if ($lastUpdate > now()->subSeconds(config('custom.rate_limiters.' . $rateLimiterKey))) {
            $sleepTime = array_key_exists($rateLimiterKey, config('custom.rate_limiters'))
                ? config('custom.rate_limiters')[$rateLimiterKey]
                : config('custom.rate_limiters.default');

            LogTransfers::info("Rate limiter activated for {$rateLimiterKey}, sleeping {$sleepTime} seconds");
            sleep($sleepTime);
        }
    }

    public function updateAssetTransfers(Asset $asset): Asset
    {
        $class = $asset->update_method->getServiceClass();

        if ($class !== null) {
            try {
                $transfers = (new $class($asset))->getTransfers();

                // Update the asset's last_update timestamp
                $asset->last_update = now();
                $asset->save();

                LogTransfers::info("Updated transfers for asset {$asset->name}: " . count($transfers) . " transfers processed");
            } catch (TransfersException $e) {
                // Re-throw TransfersException as-is since it already has all the details
                throw $e;
            } catch (Exception $e) {
                // Wrap generic exceptions in TransfersException
                throw new TransfersException(
                    $asset,
                    $e->getMessage(),
                    null,
                    null,
                    ['type' => 'transfer_update']
                );
            }
        }

        return $asset;
    }
}
