<?php

namespace App\Filament\Resources\TaxonomyResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TaxonomyResource;

class EditTaxonomy extends EditRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('manage_tags')
            //     ->label('Manage Tags')
            //     ->url(fn() => TaxonomyTagResource::getUrl('index', ['taxonomy' => $this->record->id]))
            //     ->icon('heroicon-o-tag'),
            Actions\DeleteAction::make(),
        ];
    }
}
