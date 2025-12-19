<?php

namespace App\Filament\Resources\ValuationHistoryResource\Pages;

use App\Filament\Resources\ValuationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValuationHistory extends EditRecord
{
    protected static string $resource = ValuationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
