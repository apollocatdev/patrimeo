<?php

namespace App\Filament\Resources\TaxonomyTagResource\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TaxonomyTagResource;

class CreateTaxonomyTag extends CreateRecord
{
    protected static string $resource = TaxonomyTagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
