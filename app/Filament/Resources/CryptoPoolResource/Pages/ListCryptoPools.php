<?php

namespace App\Filament\Resources\CryptoPoolResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CryptoPoolResource;
use Filament\Resources\Pages\ListRecords;

class ListCryptoPools extends ListRecords
{
    protected static string $resource = CryptoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

