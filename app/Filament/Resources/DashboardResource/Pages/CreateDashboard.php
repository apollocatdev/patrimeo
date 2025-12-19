<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\DashboardResource;

class CreateDashboard extends CreateRecord
{
    protected static string $resource = DashboardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Create the dashboard record
        $dashboard = $this->getModel()::create([
            'navigation_title' => $data['navigation_title'],
            'n_columns' => $data['n_columns'],
            'user_id' => Auth::user()->id,
        ]);

        // Handle widget relationships
        $this->updateWidgetRelationships($dashboard, $data);

        return $dashboard;
    }

    protected function updateWidgetRelationships($dashboard, array $data): void
    {
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
