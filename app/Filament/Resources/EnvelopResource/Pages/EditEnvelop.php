<?php

namespace App\Filament\Resources\EnvelopResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\EnvelopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvelop extends EditRecord
{
    protected static string $resource = EnvelopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
