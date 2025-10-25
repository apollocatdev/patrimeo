<?php

namespace App\Filament\Resources\ValuationHistoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ValuationHistoryResource;
use App\Filament\Resources\ValuationHistoryResource\Widgets\ValuationHistoryChart;

class ListValuationHistories extends ListRecords
{
    protected static string $resource = ValuationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $valuationId = request()->get('valuation');
        if ($valuationId) {
            return [
                ValuationHistoryChart::make(['valuationId' => $valuationId]),
            ];
        }
        return [];
    }

    protected function getFooterWidgets(): array
    {
        $valuationId = request()->get('valuation');
        if ($valuationId) {
            return [
                ValuationHistoryChart::make(['valuationId' => $valuationId]),
            ];
        }
        return [];
    }
}
