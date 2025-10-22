<?php

namespace App\Filament\Resources\EnvelopTypeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\EnvelopTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvelopType extends EditRecord
{
    protected static string $resource = EnvelopTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
