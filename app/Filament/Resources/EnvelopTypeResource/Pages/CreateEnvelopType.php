<?php

namespace App\Filament\Resources\EnvelopTypeResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EnvelopTypeResource;

class CreateEnvelopType extends CreateRecord
{
    protected static string $resource = EnvelopTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
