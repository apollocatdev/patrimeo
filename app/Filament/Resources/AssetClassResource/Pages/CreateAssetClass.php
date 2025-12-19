<?php

namespace App\Filament\Resources\AssetClassResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\AssetClassResource;

class CreateAssetClass extends CreateRecord
{
    protected static string $resource = AssetClassResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
