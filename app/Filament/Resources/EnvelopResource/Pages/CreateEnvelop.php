<?php

namespace App\Filament\Resources\EnvelopResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EnvelopResource;

class CreateEnvelop extends CreateRecord
{
    protected static string $resource = EnvelopResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
