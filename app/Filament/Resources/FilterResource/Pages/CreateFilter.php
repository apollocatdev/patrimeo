<?php

namespace App\Filament\Resources\FilterResource\Pages;

use App\Filament\Resources\FilterResource;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;

class CreateFilter extends CreateRecord
{
    protected static string $resource = FilterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
