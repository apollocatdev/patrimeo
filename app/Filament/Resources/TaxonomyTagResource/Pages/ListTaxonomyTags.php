<?php

namespace App\Filament\Resources\TaxonomyTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TaxonomyTagResource;

class ListTaxonomyTags extends ListRecords
{
    protected static string $resource = TaxonomyTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
