<?php

namespace App\Filament\Resources\CotationHistoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CotationHistoryResource;
use App\Filament\Resources\CotationHistoryResource\Widgets\CotationHistoryChart;

class ListCotationHistories extends ListRecords
{
    protected static string $resource = CotationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $cotationId = null;
        if (
            $this->tableFilters &&
            array_key_exists('cotation', $this->tableFilters) &&
            $this->tableFilters['cotation'] !== null
        ) {
            $cotationId = (int) $this->tableFilters['cotation'];
        }

        return [
            CotationHistoryChart::make(['cotationId' => $cotationId]),
        ];
    }

    public function updatedTableFilters(): void
    {
        // Dispatch event to refresh the widget
        $this->dispatch('refreshWidget');
    }
}
