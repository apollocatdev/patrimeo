<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DashboardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDashboards extends ListRecords
{
    protected static string $resource = DashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
