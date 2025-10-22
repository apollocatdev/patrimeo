<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\User;
use App\Models\Cotation;
use App\Models\Notification;
use App\Models\CotationUpdate;
use App\Helpers\Logs\LogCotations;
use App\Data\UpdateCotationsStatus;
use App\Enums\CotationUpdateMethod;
use App\Exceptions\CotationException;
use App\Settings\CotationUpdateSettings;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\TimeoutExceededException;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class SyncCotations implements ShouldQueue
{
    use Queueable;

    protected array $rateLimiters;
    protected ?array $cotationNames;
    protected ?int $userId;
    protected CotationUpdateSettings $settings;

    /**
     * Create a new job instance.
     */
    public function __construct(?array $cotationNames = null, ?int $userId = null)
    {
        $this->rateLimiters = [];
        $this->cotationNames = $cotationNames;
        $this->userId = $userId;
        $this->settings = FilamentSettings::getSettingForUser(CotationUpdateSettings::class, $this->userId);
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Set user context for logging
            LogCotations::setCurrentUserId($this->userId);

            // Log job start
            $triggerType = $this->cotationNames ? 'manual' : 'automatic';
            $userInfo = $this->userId ? " for user {$this->userId}" : '';
            LogCotations::info("Starting cotation update ({$triggerType}){$userInfo}");

            UpdateCotationsStatus::update($this->userId, 'updating');

            $cotations = $this->getCotations();

            UpdateCotationsStatus::updateProgress($this->userId, count($cotations), 0);

            foreach ($cotations as $i => $cotation) {
                $this->sleepIfNeeded($cotation);
                try {
                    $cotation = $this->updateCotation($cotation);
                    $this->rateLimiters[$cotation->rate_limiter_key] = $cotation->updated_at;
                } catch (CotationException $e) {
                    LogCotations::error("Failed to update cotation {$cotation->name}: " . $e->getMessage());
                }
                UpdateCotationsStatus::updateProgress($this->userId, count($cotations), $i + 1);
            }
            UpdateAllValues::dispatch($this->userId);

            // Mark job as completed in cache
            UpdateCotationsStatus::update($this->userId, 'done');
        } catch (Exception $e) {
            LogCotations::error("Critical error in cotation sync job: " . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            UpdateCotationsStatus::update($this->userId, 'failed');
            UpdateAllValues::dispatch($this->userId);
        }
    }

    protected function getCotations(): Collection
    {
        $query = Cotation::query();
        $query = $this->userId ? $query->where('user_id', $this->userId) : $query;
        $query = $this->cotationNames ? $query->whereIn('name', $this->cotationNames) : $query;

        // Exclude cotations that were updated recently based on min_hours_between_updates setting
        if ($this->settings->minHoursBetweenUpdates > 0) {
            $query->where(function ($q) {
                $q->whereNull('last_update')
                    ->orWhere('last_update', '<', now()->subHours($this->settings->minHoursBetweenUpdates));
            });
        }

        // Get all cotations to process
        $cotations = $query->get();
        return $cotations;
    }



    protected function sleepIfNeeded(Cotation $cotation): void
    {
        if (! array_key_exists($cotation->rate_limiter_key, $this->rateLimiters)) {
            return;
        }
        $lastUpdate = $this->rateLimiters[$cotation->rate_limiter_key];
        $rateLimitSeconds = $this->settings->getRateLimitForService($cotation->rate_limiter_key);

        if ($lastUpdate > now()->subSeconds($rateLimitSeconds)) {
            LogCotations::info("Rate limiter activated for {$cotation->rate_limiter_key}, sleeping {$rateLimitSeconds} seconds");
            sleep($rateLimitSeconds);
        }
    }

    public function updateCotation(Cotation $cotation): Cotation
    {
        $cotationUpdate = CotationUpdate::create([
            'cotation_id' => $cotation->id,
            'user_id' => $cotation->user_id,
            'date' => now(),
            'status' => 'pending',
            'message' => null,
            'value' => null,
        ]);

        $class = $cotation->update_method->getServiceClass();

        if ($class !== null) {
            try {
                $cotation->value = (new $class($cotation))->getQuote();
            } catch (Exception $e) {
                // Wrap all exceptions in CotationException for consistent handling
                throw new CotationException(
                    $cotation,
                    $e->getMessage(),
                    null,
                    null,
                    ['cotation_update_id' => $cotationUpdate->id, 'type' => 'cotation_update']
                );
            }
            $cotation->last_update = now();
            $cotation->saveQuietly();
            $cotationUpdate->update([
                'status' => 'success',
                'message' => null,
                'value' => $cotation->value,
            ]);

            // Log successful cotation update
            LogCotations::info("Cotation updated: {$cotation->name} = {$cotation->value} ({$cotation->update_method->value})");
        }
        if ($class === null) {
            if (($cotation->update_method === CotationUpdateMethod::FIXED) || ($cotation->update_method === CotationUpdateMethod::MANUAL)) {
                $cotation->last_update = now();
                $cotation->saveQuietly();

                $cotationUpdate->update([
                    'status' => 'success',
                    'message' => null,
                    'value' => $cotation->value,
                ]);

                // Log successful cotation update for fixed/manual methods
                LogCotations::info("Cotation updated: {$cotation->name} = {$cotation->value} ({$cotation->update_method->value})");
            } else {
                throw new CotationException(
                    $cotation,
                    'Cotation update method not found',
                    null,
                    null,
                    ['cotation_update_id' => $cotationUpdate->id, 'type' => 'cotation_update']
                );
            }
        }
        return $cotation;
    }

    public function failed(?Throwable $exception): void
    {
        LogCotations::setCurrentUserId($this->userId);

        $errorType = $exception instanceof TimeoutExceededException ? 'timeout' : 'error';

        LogCotations::error("Cotation sync job failed ({$errorType}): " . $exception->getMessage() ?? 'Unknown error', [
            'exception' => $exception,
            'user_id' => $this->userId
        ]);

        Notification::createError(
            User::find($this->userId),
            'Cotation Sync job failed (' . $errorType . ')',
            $exception->getMessage() ?? 'Unknown error',
            []
        );

        $pendingUpdates = CotationUpdate::where('user_id', $this->userId)->where('status', 'pending')->get();
        foreach ($pendingUpdates as $update) {
            $update->status = 'failed';
            $update->message = $exception->getMessage() ?? 'Unknown error';
            $update->save();
        }

        UpdateCotationsStatus::update($this->userId, 'failed');
        UpdateAllValues::dispatch($this->userId);
    }
}
