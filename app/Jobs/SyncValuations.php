<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\User;
use App\Models\Valuation;
use App\Models\Notification;
use App\Models\ValuationUpdate;
use App\Helpers\Logs\LogValuations;
use App\Data\UpdateValuationsStatus;
use App\Enums\ValuationUpdateMethod;
use App\Exceptions\ValuationException;
use App\Settings\ValuationUpdateSettings;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\TimeoutExceededException;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class SyncValuations implements ShouldQueue
{
    use Queueable;

    protected array $rateLimiters;
    protected ?array $valuationNames;
    protected ?int $userId;
    protected ValuationUpdateSettings $settings;

    /**
     * Create a new job instance.
     */
    public function __construct(?array $valuationNames = null, ?int $userId = null)
    {
        $this->rateLimiters = [];
        $this->valuationNames = $valuationNames;
        $this->userId = $userId;
        $this->settings = FilamentSettings::getSettingForUser(ValuationUpdateSettings::class, $this->userId);
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Set user context for logging
            LogValuations::setCurrentUserId($this->userId);

            // Log job start
            $triggerType = $this->valuationNames ? 'manual' : 'automatic';
            $userInfo = $this->userId ? " for user {$this->userId}" : '';
            LogValuations::info("Starting valuation update ({$triggerType}){$userInfo}");

            UpdateValuationsStatus::update($this->userId, 'updating');

            $valuations = $this->getValuations();

            UpdateValuationsStatus::updateProgress($this->userId, count($valuations), 0);

            foreach ($valuations as $i => $valuation) {
                $this->sleepIfNeeded($valuation);
                try {
                    $valuation = $this->updateValuation($valuation);
                    $this->rateLimiters[$valuation->update_method->getRateLimiterKey($valuation->update_data)] = $valuation->updated_at;
                } catch (ValuationException $e) {
                    LogValuations::error("Failed to update valuation {$valuation->name}: " . $e->getMessage());
                }
                UpdateValuationsStatus::updateProgress($this->userId, count($valuations), $i + 1);
            }
            UpdateAllValues::dispatch($this->userId);

            // Mark job as completed in cache
            UpdateValuationsStatus::update($this->userId, 'done');
        } catch (Exception $e) {
            LogValuations::error("Critical error in valuation sync job: " . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            UpdateValuationsStatus::update($this->userId, 'failed');
            UpdateAllValues::dispatch($this->userId);
        }
    }

    protected function getValuations(): Collection
    {
        $query = Valuation::query();
        $query = $this->userId ? $query->where('user_id', $this->userId) : $query;
        $query = $this->valuationNames ? $query->whereIn('name', $this->valuationNames) : $query;

        // Exclude valuations that were updated recently based on min_hours_between_updates setting
        if ($this->settings->minHoursBetweenUpdates > 0) {
            $query->where(function ($q) {
                $q->whereNull('last_update')
                    ->orWhere('last_update', '<', now()->subHours($this->settings->minHoursBetweenUpdates));
            });
        }

        // Get all valuations to process
        $valuations = $query->get();
        return $valuations;
    }



    protected function sleepIfNeeded(Valuation $valuation): void
    {
        $rateLimiterKey = $valuation->update_method->getRateLimiterKey($valuation->update_data);
        if (! array_key_exists($rateLimiterKey, $this->rateLimiters)) {
            return;
        }
        $lastUpdate = $this->rateLimiters[$rateLimiterKey];
        $rateLimitSeconds = $this->settings->getRateLimitForService($rateLimiterKey);

        if ($lastUpdate > now()->subSeconds($rateLimitSeconds)) {
            LogValuations::info("Rate limiter activated for {$rateLimiterKey}, sleeping {$rateLimitSeconds} seconds");
            sleep($rateLimitSeconds);
        }
    }

    public function updateValuation(Valuation $valuation): Valuation
    {
        $valuationUpdate = ValuationUpdate::create([
            'valuation_id' => $valuation->id,
            'user_id' => $valuation->user_id,
            'date' => now(),
            'status' => 'pending',
            'message' => null,
            'value' => null,
        ]);

        $class = $valuation->update_method->getServiceClass();

        if ($class !== null) {
            try {
                $valuation->value = (new $class($valuation))->getQuote();
            } catch (Exception $e) {
                // Wrap all exceptions in ValuationException for consistent handling
                throw new ValuationException(
                    $valuation,
                    $e->getMessage(),
                    null,
                    null,
                    ['valuation_update_id' => $valuationUpdate->id, 'type' => 'valuation_update']
                );
            }
            $valuation->last_update = now();
            $valuation->saveQuietly();
            $valuationUpdate->update([
                'status' => 'success',
                'message' => null,
                'value' => $valuation->value,
            ]);

            // Log successful valuation update
            LogValuations::info("Valuation updated: {$valuation->name} = {$valuation->value} ({$valuation->update_method->value})");
        }
        if ($class === null) {
            if (($valuation->update_method === ValuationUpdateMethod::FIXED) || ($valuation->update_method === ValuationUpdateMethod::MANUAL)) {
                $valuation->last_update = now();
                $valuation->saveQuietly();

                $valuationUpdate->update([
                    'status' => 'success',
                    'message' => null,
                    'value' => $valuation->value,
                ]);

                // Log successful valuation update for fixed/manual methods
                LogValuations::info("Valuation updated: {$valuation->name} = {$valuation->value} ({$valuation->update_method->value})");
            } else {
                throw new ValuationException(
                    $valuation,
                    'Valuation update method not found',
                    null,
                    null,
                    ['valuation_update_id' => $valuationUpdate->id, 'type' => 'valuation_update']
                );
            }
        }
        return $valuation;
    }

    public function failed(?Throwable $exception): void
    {
        LogValuations::setCurrentUserId($this->userId);

        $errorType = $exception instanceof TimeoutExceededException ? 'timeout' : 'error';

        LogValuations::error("Valuation sync job failed ({$errorType}): " . $exception->getMessage() ?? 'Unknown error', [
            'exception' => $exception,
            'user_id' => $this->userId
        ]);

        Notification::createError(
            User::find($this->userId),
            'Valuation Sync job failed (' . $errorType . ')',
            $exception->getMessage() ?? 'Unknown error',
            []
        );

        $pendingUpdates = ValuationUpdate::where('user_id', $this->userId)->where('status', 'pending')->get();
        foreach ($pendingUpdates as $update) {
            $update->status = 'failed';
            $update->message = $exception->getMessage() ?? 'Unknown error';
            $update->save();
        }

        UpdateValuationsStatus::update($this->userId, 'failed');
        UpdateAllValues::dispatch($this->userId);
    }
}
