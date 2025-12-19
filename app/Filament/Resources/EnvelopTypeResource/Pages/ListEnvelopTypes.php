<?php

namespace App\Filament\Resources\EnvelopTypeResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\EnvelopTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvelopTypes extends ListRecords
{
    protected static string $resource = EnvelopTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
