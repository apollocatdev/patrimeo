<?php

namespace App\Filament\Resources\TaxonomyTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TaxonomyTagResource;

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
