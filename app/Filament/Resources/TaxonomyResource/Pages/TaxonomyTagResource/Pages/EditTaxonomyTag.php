<?php

namespace App\Filament\Resources\TaxonomyResource\Pages\TaxonomyTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TaxonomyResource\Pages\TaxonomyTagResource;

class EditTaxonomyTag extends EditRecord
{
    protected static string $resource = TaxonomyTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
