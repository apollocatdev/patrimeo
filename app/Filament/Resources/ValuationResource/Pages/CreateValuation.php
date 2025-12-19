<?php

namespace App\Filament\Resources\ValuationResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Valuation;
use App\Enums\ValuationUpdateMethod;
use App\Exceptions\ValuationException;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ValuationResource;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;


class CreateValuation extends CreateRecord
{
    protected static string $resource = ValuationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
