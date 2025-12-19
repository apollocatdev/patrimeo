<?php

namespace App\Charts\Charts;

use App\Models\Envelop;
use App\Models\Valuation;
use App\Enums\Widgets\WidgetType;
use App\Models\AssetClass;
use App\Models\EnvelopType;
use App\Models\Taxonomy;
use App\Charts\AbstractChart;
use App\Helpers\PortfolioCompute\PortfolioState;
use Illuminate\Support\Collection;
use App\Enums\Widgets\WidgetDimension;
use App\Enums\Widgets\WidgetEntity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class TreemapAssetsDistribution extends AbstractChart
{
    public static WidgetType $type = WidgetType::CHART_TREEMAP_ASSETS_DISTRIBUTION;

    protected function compute()
    {
        $dimensionField = $this->getDimensionField();
        $dimensionRelation = $this->getDimensionRelation();
        $uniqueDimensions = $this->getUniqueDimensions();

        $series = [];
        $ps = new PortfolioState(null, $this->widget->filters);
        $assets = $ps->assets();

        // Handle taxonomy grouping differently since it's a many-to-many relationship
        if ($this->widget->parameters['dimension'] === 'taxonomy') {
            foreach ($uniqueDimensions as $dimension) {
                $sum = 0;
                foreach ($assets as $asset) {
                    $assetTags = $asset->tags()->pluck('name');
                    if ($assetTags->contains($dimension)) {
                        $sum += $asset->value;
                    }
                }
                $series[] = [
                    'x' => $dimension,
                    'y' => $sum,
                ];
            }
        } else {
            // For other dimensions, use groupBy
            $groupedAssets = $assets->groupBy($dimensionRelation . '.' . $dimensionField);

            foreach ($uniqueDimensions as $dimension) {
                $groupedAssetsForDimension = $groupedAssets->dataGet($dimension);
                $sum = $groupedAssetsForDimension ? $groupedAssetsForDimension->sum('value') : 0;
                $series[] = [
                    'x' => $dimension,
                    'y' => $sum,
                ];
            }
        }

        $this->options = [
            'chart' => [
                'type' => 'treemap',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [['data' => $series]],
            'legend' => ['show' => false],
            // 'labels' => $uniqueDimensions->toArray(),
        ];
    }

    protected function getDimensionField(): string
    {
        switch ($this->widget->parameters['dimension']) {
            case 'class':
                return 'name';
            case 'envelop':
                return 'name';
            case 'envelop_type':
                return 'name';
            case 'currency':
                return 'currency';
            case 'taxonomy':
                return 'name';
        }
        return '';
    }
    protected function getDimensionRelation(): string
    {
        switch ($this->widget->parameters['dimension']) {
            case 'class':
                return 'class';
            case 'envelop':
                return 'envelop';
            case 'envelop_type':
                return 'envelop.type';
            case 'currency':
                return 'currency';
            case 'taxonomy':
                return 'tags';
        }
        return '';
    }

    protected function getUniqueDimensions(): Collection
    {
        switch ($this->widget->parameters['dimension']) {
            case 'class':
                return AssetClass::all()->pluck('name');
                break;
            case 'envelop':
                return Envelop::all()->pluck('name');
                break;
            case 'envelop_type':
                return EnvelopType::all()->pluck('name');
                break;
            case 'currency':
                return Valuation::all()->pluck('currency');
                break;
            case 'taxonomy':
                if (isset($this->widget->parameters['taxonomy'])) {
                    $taxonomy = Taxonomy::find($this->widget->parameters['taxonomy']);
                    if ($taxonomy) {
                        return $taxonomy->tags()->pluck('name');
                    }
                }
                return collect([]);
                break;
        }
        return collect([]);
    }

    public static function form(): array
    {
        return [
            'dimension' => Select::make('dimension')
                ->label(__('Dimension'))
                ->options(WidgetDimension::class)
                ->required(),
            'taxonomy' => TextInput::make('taxonomy')
                ->label(__('Taxonomy ID'))
                ->numeric()
                ->visible(function (Get $get) {
                    return $get('dimension') === WidgetDimension::TAXONOMY->value;
                }),
        ];
    }
}
