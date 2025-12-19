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
        $valuationId = null;
        if (
            $this->tableFilters &&
            array_key_exists('valuation', $this->tableFilters) &&
            $this->tableFilters['valuation'] !== null
        ) {
            $valuationId = (int) $this->tableFilters['valuation'];
        }

        return [
            ValuationHistoryChart::make(['valuationId' => $valuationId]),
        ];
    }

    public function updatedTableFilters(): void
    {
        // Dispatch event to refresh the widget
        $this->dispatch('refreshWidget');
    }
}
