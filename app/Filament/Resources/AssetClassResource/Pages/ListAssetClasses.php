<?php

namespace App\Filament\Resources\AssetClassResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AssetClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetClasses extends ListRecords
{
    protected static string $resource = AssetClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
