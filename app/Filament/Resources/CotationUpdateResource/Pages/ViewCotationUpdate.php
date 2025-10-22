<?php

namespace App\Filament\Resources\CotationUpdateResource\Pages;

use App\Filament\Resources\CotationUpdateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCotationUpdate extends ViewRecord
{
    protected static string $resource = CotationUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
