<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('deleteAll')
                ->label('Delete All')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete All Notifications')
                ->modalDescription('Are you sure you want to delete all notifications? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete all')
                ->action(function () {
                    \App\Models\Notification::query()->delete();
                })
                ->after(function () {
                    // Refresh the page after deletion
                    redirect()->route('filament.admin.resources.notifications.index');
                }),
        ];
    }
}
