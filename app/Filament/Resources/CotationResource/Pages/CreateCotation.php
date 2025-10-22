<?php

namespace App\Filament\Resources\CotationResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Cotation;
use App\Enums\CotationUpdateMethod;
use App\Exceptions\CotationException;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CotationResource;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;


class CreateCotation extends CreateRecord
{
    protected static string $resource = CotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
