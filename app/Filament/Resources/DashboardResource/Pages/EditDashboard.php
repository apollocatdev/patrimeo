<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DashboardResource;
use App\Models\Widget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditDashboard extends EditRecord
{
    protected static string $resource = DashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing widgets and separate them by type
        $dashboard = $this->record;
        $widgets = $dashboard->widgets()->get();

        $statsWidgets = [];
        $chartWidgets = [];

        foreach ($widgets as $widget) {
            if (str_starts_with($widget->type->value, 'stat_')) {
                $statsWidgets[] = [
                    'widget_id' => $widget->id,
                ];
            } else {
                $chartWidgets[] = [
                    'widget_id' => $widget->id,
                    'column_span' => $widget->pivot->column_span ?? '2',
                ];
            }
        }

        $data['stats_widgets'] = $statsWidgets;
        $data['chart_widgets'] = $chartWidgets;

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Update the dashboard record
        $record->update([
            'navigation_title' => $data['navigation_title'],
            'n_columns' => $data['n_columns'],
        ]);

        // Handle widget relationships
        $this->updateWidgetRelationships($record, $data);

        return $record;
    }

    protected function updateWidgetRelationships($dashboard, array $data): void
    {
        // Clear existing relationships
        $dashboard->widgets()->detach();

        $sortOrder = 0;

        // Add stats widgets first
        if (isset($data['stats_widgets'])) {
            foreach ($data['stats_widgets'] as $widgetData) {
                $dashboard->widgets()->attach($widgetData['widget_id'], [
                    'sort' => $sortOrder++,
                    'column_span' => null,
                ]);
            }
        }

        // Add chart widgets after stats widgets
        if (isset($data['chart_widgets'])) {
            foreach ($data['chart_widgets'] as $widgetData) {
                $dashboard->widgets()->attach($widgetData['widget_id'], [
                    'sort' => $sortOrder++,
                    'column_span' => $widgetData['column_span'] ?? '2',
                ]);
            }
        }
    }
}
