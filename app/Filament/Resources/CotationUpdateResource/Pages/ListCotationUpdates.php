<?php

namespace App\Filament\Resources\CotationUpdateResource\Pages;

use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CotationUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCotationUpdates extends ListRecords
{
    protected static string $resource = CotationUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    // protected function getTableQuery(): Builder
    // {
    //     return parent::getTableQuery()->where('status', 'error');
    // }
}
