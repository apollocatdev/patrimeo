<?php

namespace App\Jobs;

use App\Models\Schedule;
use Cron\CronExpression;
use TelegramBot\Api\BotApi;
use App\Jobs\SyncValuations;
use App\Models\Notification;
use App\Enums\ScheduleAction;
use App\Jobs\SyncCryptoPools;
use App\Jobs\SyncTransactions;
use App\Settings\EmailSettings;
use App\Helpers\Logs\LogScheduler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Settings\IntegrationsSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

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
            LogScheduler::error("Invalid cron expression for schedule {$schedule->id}: {$schedule->cron}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function executeSchedule(Schedule $schedule): void
    {
        LogScheduler::info("Executing schedule: {$schedule->name}");

        foreach ($schedule->actions->actions as $action) {
            $this->executeAction($schedule, $action);
        }
    }

    private function executeAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        switch ($action->action) {
            case ScheduleAction::UPDATE:
                $this->executeUpdateAction($schedule, $action);
                break;
            case ScheduleAction::NOTIFY_IN_APP:
                $this->executeNotifyInAppAction($schedule, $action);
                break;
            case ScheduleAction::NOTIFY_BY_EMAIL:
                $this->executeNotifyByEmailAction($schedule, $action);
                break;
            case ScheduleAction::NOTIFY_BY_TELEGRAM:
                $this->executeNotifyByTelegramAction($schedule, $action);
                break;
            default:
                LogScheduler::warning("Unknown action type: {$action->action} for schedule {$schedule->id}");
        }
    }

    private function getFullMessage(Schedule $schedule, \App\Data\Schedules\Action $action): string
    {
        $message = $action->message;
        if ($schedule->assets->isNotEmpty()) {
            $message .= ' - ' . implode(', ', $schedule->assets->pluck('name')->toArray());
        } elseif ($schedule->valuations->isNotEmpty()) {
            $message .= ' - ' . implode(', ', $schedule->valuations->pluck('name')->toArray());
        }

        return $message;
    }

    private function executeUpdateAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        $valuations = $schedule->valuations;
        $assets = $schedule->assets;
        $cryptoPools = $schedule->cryptoPools;
        $this->executeNotifyInAppAction($schedule, $action);

        if ($valuations->isNotEmpty()) {
            $valuationNames = $valuations->pluck('name')->toArray();
            SyncValuations::dispatch($valuationNames, $schedule->user_id);
        }

        if ($assets->isNotEmpty()) {
            $assetNames = $assets->pluck('name')->toArray();
            SyncTransactions::dispatch($assetNames, $schedule->user_id);
        }

        if ($cryptoPools->isNotEmpty()) {
            $cryptoPoolNames = $cryptoPools->pluck('name')->toArray();
            SyncCryptoPools::dispatch($cryptoPoolNames, $schedule->user_id);
        }
    }

    private function executeNotifyInAppAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        $data = $schedule->assets->isNotEmpty() ? ['type' => 'scheduled_asset_update', 'asset_names' => $schedule->assets->pluck('name')->toArray()] : ['type' => 'scheduled_valuation_update', 'valuation_names' => $schedule->valuations->pluck('name')->toArray()];
        Notification::createForUser(
            $schedule->user,
            $schedule->name,
            $this->getFullMessage($schedule, $action),
            'info',
            $data
        );
        LogScheduler::info("Created in-app notification for schedule {$schedule->name}");
    }

    private function executeNotifyByEmailAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
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
        $this->executeNotifyInAppAction($schedule, $action);

        try {
            // Configure SMTP settings for this email
            /** @var EmailSettings $smtpSettings */
            $smtpSettings = FilamentSettings::getSettingForUser(EmailSettings::class, $user->id);

            // $mailConfig = $smtpSettings->toMailConfig();
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $smtpSettings->host);
            Config::set('mail.mailers.smtp.port', $smtpSettings->port);
            Config::set('mail.mailers.smtp.encryption', $smtpSettings->encryption);
            Config::set('mail.mailers.smtp.username', $smtpSettings->username);
            Config::set('mail.mailers.smtp.password', $smtpSettings->password);

            Mail::to($user->email)->send(new \App\Mail\ScheduleNotification($data));
            LogScheduler::info("Sent email notification for schedule {$schedule->name} to {$user->email}");
        } catch (\Exception $e) {
            LogScheduler::error("Failed to send email for schedule {$schedule->name}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function executeNotifyByTelegramAction(Schedule $schedule, \App\Data\Schedules\Action $action): void
    {
        $user = $schedule->user;

        $data = [
            'message' => $action->message,
            'schedule' => $schedule,
            'valuations' => $schedule->valuations,
            'assets' => $schedule->assets,
        ];

        $this->executeNotifyInAppAction($schedule, $action);
        /** @var IntegrationsSettings $integrationsSettings */
        $integrationsSettings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $user->id);
        $telegramBotToken = $integrationsSettings->telegramBotToken;
        $telegramChatId = $integrationsSettings->telegramChatId;
        if (!$telegramBotToken || !$telegramChatId) {
            LogScheduler::error("Telegram bot token or chat id is not set");
            return;
        }
        try {
            $bot = new BotApi($telegramBotToken);
            $bot->sendMessage($telegramChatId, $this->getFullMessage($schedule, $action));
            LogScheduler::info("Sent telegram notification for schedule {$schedule->name} to user {$user->id}");
        } catch (\Exception $e) {
            LogScheduler::error("Failed to send telegram notification for schedule {$schedule->name}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
