<?php

namespace App\Filament\Resources\CotationResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use App\Models\Cotation;
use App\Enums\CotationUpdateMethod;
use App\Filament\Resources\CotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CotationException;

class EditCotation extends EditRecord
{
    protected static string $resource = CotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
