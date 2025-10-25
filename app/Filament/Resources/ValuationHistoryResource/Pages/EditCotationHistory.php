<?php

namespace App\Filament\Resources\CotationHistoryResource\Pages;

use App\Filament\Resources\CotationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCotationHistory extends EditRecord
{
    protected static string $resource = CotationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
