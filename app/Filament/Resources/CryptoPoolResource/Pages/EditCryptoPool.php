<?php

namespace App\Filament\Resources\CryptoPoolResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CryptoPoolResource;
use Filament\Resources\Pages\EditRecord;

class EditCryptoPool extends EditRecord
{
    protected static string $resource = CryptoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
