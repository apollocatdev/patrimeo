<?php

namespace App\Filament\Resources\ValuationUpdateResource\Pages;

use App\Filament\Resources\ValuationUpdateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewValuationUpdate extends ViewRecord
{
    protected static string $resource = ValuationUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
