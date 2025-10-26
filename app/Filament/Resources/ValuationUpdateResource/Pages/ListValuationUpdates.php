<?php

namespace App\Filament\Resources\ValuationUpdateResource\Pages;

use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ValuationUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListValuationUpdates extends ListRecords
{
    protected static string $resource = ValuationUpdateResource::class;

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
