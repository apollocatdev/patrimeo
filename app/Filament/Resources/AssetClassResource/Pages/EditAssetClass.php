<?php

namespace App\Filament\Resources\AssetClassResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AssetClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetClass extends EditRecord
{
    protected static string $resource = AssetClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
