<?php

namespace App\Filament\Resources\AssetResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\Filter;
use App\Enums\Filters\FilterEntity;
use Illuminate\Database\Eloquent\Builder;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Assets'),
        ];

        // Get all asset filters for the current user
        $assetFilters = Filter::where('entity', FilterEntity::ASSETS)->get();

        foreach ($assetFilters as $filter) {
            $tabs[$filter->id] = Tab::make($filter->name)
                ->modifyQueryUsing(fn(Builder $query) => $filter->applyToQuery($query));
        }

        return $tabs;
    }
}
