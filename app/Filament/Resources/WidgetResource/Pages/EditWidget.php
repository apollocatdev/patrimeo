<?php

namespace App\Filament\Resources\WidgetResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions;
use App\Models\Widget;
use App\Enums\Widgets\WidgetType;
use App\Models\WidgetStatOverview;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\WidgetResource;

class EditWidget extends EditRecord
{
    protected static string $resource = WidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     $widget = Widget::with('widgetable')->findOrFail($data['id']);

    //     $data['type'] = $data['widgetable_type'] === WidgetStatOverview::class ? WidgetType::STAT_OVERVIEW : WidgetType::CHART;
    //     $data['stats'] = [];
    //     if ($data['type'] === WidgetType::STAT_OVERVIEW) {
    //         $widgetStats = $widget->widgetable->stats;
    //         foreach ($widgetStats as $widgetStat) {
    //             $stat = [
    //                 'title' => $widgetStat->title,
    //                 'description' => $widgetStat->description,
    //                 'entity' => $widgetStat->entity,
    //                 'operation' => $widgetStat->operation,
    //                 'column' => $widgetStat->column,
    //                 'filters' => []
    //             ];
    //             if ($widgetStat->filters !== null) {
    //                 foreach ($widgetStat->filters as $filter) {
    //                     $stat['filters'][] = [
    //                         'field' => $filter->field,
    //                         'operator' => $filter->operator,
    //                         'value' => $filter->value,
    //                     ];
    //                 }
    //             }

    //             $data['stats'][] = $stat;

    //         }
    //     }
    //     return $data;
    // }

    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     $widgetData = [
    //         'title' => $data['title'],
    //         'description' => $data['description'],
    //         'type' => $data['type'],
    //     ];
    //     if ($data['type'] === WidgetType::STAT_OVERVIEW) {
    //         $widgetStats = [];
    //         foreach ($data['stats'] as $stat) {
    //             $widgetStat = [
    //                 'title' => $stat['title'],
    //                 'description' => $stat['description'],
    //                 'entity' => $stat['entity'],
    //                 'operation' => $stat['operaton'],
    //                 'column' => $stat['column'],
    //                 'filters' => []
    //             ];
    //             foreach ($stat['filters'] as $filter) {
    //                 $widgetStat['filters'][] = [
    //                     'field' => $filter['field'],
    //                     'operator' => $filter['operator'],
    //                     'value' => $filter['value'],
    //                 ];
    //             }
    //             $widgetStats[] = $widgetStat;
    //         }
    //     }
    //     $record->update($widgetData);

    //    return $record; 
    // }
}
