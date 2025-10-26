<?php

namespace App\Filament\Resources\ValuationUpdateResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ValuationUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValuationUpdate extends EditRecord
{
    protected static string $resource = ValuationUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
