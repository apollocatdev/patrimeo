<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class ViewLogs extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected string $view = 'filament.pages.view-logs';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 5;

    public string $logContent = '';
    public string $selectedChannel = 'valuations';

    public function mount(): void
    {
        $this->loadLogContent();
    }

    public function loadLogContent(): void
    {
        $logPath = storage_path("logs/{$this->selectedChannel}.log");

        if (File::exists($logPath)) {
            $this->logContent = File::get($logPath);
        } else {
            $this->logContent = "No log file found for channel: {$this->selectedChannel}";
        }
    }

    public function updatedSelectedChannel(): void
    {
        $this->loadLogContent();
    }

    public function clearAllLogs(): void
    {
        $logFiles = ['valuations.log', 'transactions.log', 'dashboards.log', 'scheduler.log', 'tools.log'];
        $deletedCount = 0;

        foreach ($logFiles as $logFile) {
            $logPath = storage_path("logs/{$logFile}");
            if (File::exists($logPath)) {
                File::delete($logPath);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            Notification::make()
                ->title('Log files cleared successfully')
                ->body("Deleted {$deletedCount} log file(s)")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('No log files found')
                ->body('No log files were found to delete')
                ->warning()
                ->send();
        }

        // Reload the current log content
        $this->loadLogContent();
    }
}
