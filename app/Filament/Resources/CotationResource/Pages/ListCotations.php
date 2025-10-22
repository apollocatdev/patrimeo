<?php

namespace App\Filament\Resources\CotationResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCotations extends ListRecords
{
    protected static string $resource = CotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

}
