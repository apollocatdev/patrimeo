<?php

namespace App\Filament\Resources\CryptoPoolResource\Pages;

use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\CryptoPoolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCryptoPool extends CreateRecord
{
    protected static string $resource = CryptoPoolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}

