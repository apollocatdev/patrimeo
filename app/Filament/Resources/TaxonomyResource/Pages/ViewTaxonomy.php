<?php

namespace App\Filament\Resources\TaxonomyResource\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TaxonomyResource;

class ViewTaxonomy extends ViewRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
