<?php

namespace App\Filament\Resources\EnvelopResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\EnvelopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvelops extends ListRecords
{
    protected static string $resource = EnvelopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
