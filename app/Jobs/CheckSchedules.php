<?php

namespace App\Jobs;

use App\Models\Schedule;
use App\Jobs\SyncValuations;
use App\Jobs\SyncTransactions;
use App\Settings\EmailSettings;
use Cron\CronExpression;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

class CheckSchedules implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schedules = Schedule::all();

        foreach ($schedules as $schedule) {
            if ($this->shouldExecuteSchedule($schedule)) {
                $this->executeSchedule($schedule);
            }
        }
    }

    private function shouldExecuteSchedule(Schedule $schedule): bool
    {
        try {
            $cron = new CronExpression($schedule->cron);
            return $cron->isDue();
        } catch (\Exception $e) {
            Log::error("Invalid cron expression for schedule {$schedule->id}: {$schedule->cron}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function executeSchedule(Schedule $schedule): void
    {
        Log::info("Executing schedule: {$schedule->name}");

        foreach ($schedule->actions as $action) {
            $this->executeAction($schedule, $action);
        }
    }

    private function executeAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        switch ($action->action) {
            case 'update':
                $this->executeUpdateAction($schedule);
                break;
            case 'email':
                $this->executeEmailAction($schedule, $action);
                break;
            case 'notify':
                $this->executeNotifyAction($schedule, $action);
                break;
            default:
                Log::warning("Unknown action type: {$action->action} for schedule {$schedule->id}");
        }
    }

    private function executeUpdateAction(Schedule $schedule): void
    {
        $valuations = $schedule->valuations;
        $assets = $schedule->assets;

        if ($valuations->isNotEmpty()) {
            $valuationNames = $valuations->pluck('name')->toArray();
            SyncValuations::dispatch($valuationNames, $schedule->user_id);
            Log::info("Dispatched valuation updates for schedule {$schedule->name}", [
                'valuations' => $valuationNames
            ]);
        }

        if ($assets->isNotEmpty()) {
            $assetNames = $assets->pluck('name')->toArray();
            SyncTransactions::dispatch($assetNames, $schedule->user_id);
            Log::info("Dispatched asset updates for schedule {$schedule->name}", [
                'assets' => $assetNames
            ]);
        }
    }

    private function executeEmailAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        $user = $schedule->user;
        $valuations = $schedule->valuations;
        $assets = $schedule->assets;

        $data = [
            'message' => $action->message,
            'schedule' => $schedule,
            'valuations' => $valuations,
            'assets' => $assets,
        ];

        try {
            // Configure SMTP settings for this email
            $smtpSettings = EmailSettings::get();
            $mailConfig = $smtpSettings->toMailConfig();
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $mailConfig['host']);
            Config::set('mail.mailers.smtp.port', $mailConfig['port']);
            Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
            Config::set('mail.mailers.smtp.username', $mailConfig['username']);
            Config::set('mail.mailers.smtp.password', $mailConfig['password']);

            Mail::to($user->email)->send(new \App\Mail\ScheduleNotification($data));
            Log::info("Sent email notification for schedule {$schedule->name} to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send email for schedule {$schedule->name}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function executeNotifyAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        $user = $schedule->user;
        $valuations = $schedule->valuations;
        $assets = $schedule->assets;

        $data = [
            'message' => $action->message,
            'schedule' => $schedule,
            'valuations' => $valuations,
            'assets' => $assets,
        ];

        try {
            $user->notify(new \App\Notifications\ScheduleNotification($data));
            Log::info("Sent notification for schedule {$schedule->name} to user {$user->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification for schedule {$schedule->name}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
