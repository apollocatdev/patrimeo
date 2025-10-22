<?php

namespace App\Filament\Resources\CotationUpdateResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CotationUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCotationUpdate extends EditRecord
{
    protected static string $resource = CotationUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
