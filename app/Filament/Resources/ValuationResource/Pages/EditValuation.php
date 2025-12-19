<?php

namespace App\Filament\Resources\ValuationResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use App\Models\Valuation;
use App\Enums\ValuationUpdateMethod;
use App\Filament\Resources\ValuationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ValuationException;

class EditValuation extends EditRecord
{
    protected static string $resource = ValuationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
