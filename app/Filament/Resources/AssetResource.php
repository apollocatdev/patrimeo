<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use App\Models\Taxonomy;
use Filament\Tables\Table;
use App\Models\TaxonomyTag;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Enums\TransferUpdateMethod;
use App\Enums\TaxonomyTypes;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\AssetResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AssetResource\Pages\EditAsset;
use App\Filament\Resources\AssetResource\Pages\ListAssets;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Filament\Resources\AssetResource\Pages\CreateAsset;
use Filament\Tables\Filters\SelectFilter;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static string | \BackedEnum | null $navigationIcon = 'hugeicons-money-bag-02';
    protected static string | \UnitEnum | null $navigationGroup = 'Portfolio';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Asset Information'))->schema([
                    TextInput::make('name')
                        ->required(),
                    Select::make('envelop_id')
                        ->relationship('envelop', 'name')
                        ->required(),
                    Select::make('class_id')
                        ->relationship('class', 'name')
                        ->required(),
                    TextInput::make('quantity')
                        ->numeric(),
                    Select::make('cotation_id')
                        ->required()
                        ->relationship('cotation', 'name'),
                ])->columns(2),

                Section::make(__('Transfer Update Parameters'))->schema([
                    Select::make('update_method')
                        ->label(__('Transfer Update Method'))
                        ->options(TransferUpdateMethod::class)
                        ->helperText(__('Method used to update transfers for this asset'))
                        ->placeholder(__('Select a transfer update method'))
                        ->live()
                        ->afterStateUpdated(fn(Select $component) => $component
                            ->getContainer()
                            ->getComponent('dynamicTransferFields')
                            ->getChildSchema()
                            ->fill()),

                    Group::make()
                        ->schema(function (Get $get): array {
                            if (empty($get('update_method'))) {
                                return [];
                            }

                            $serviceClass = $get('update_method')->getServiceClass();

                            if (!$serviceClass) {
                                return [];
                            }

                            return [
                                Group::make()->schema($serviceClass::getFields())->statePath('update_data'),
                            ];
                        })->key('dynamicTransferFields'),

                    Select::make('schedules')
                        ->relationship('schedules', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Choose which schedules apply to this asset'),
                ])->columns(2),

                Section::make(__('Taxonomies & Tags'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // Non-weighted taxonomies - single select for each taxonomy
                        Fieldset::make(__('Unweighted Taxonomies'))
                            ->schema(function () {
                                $nonWeightedTaxonomies = Taxonomy::where('weighted', false)
                                    ->where('type', TaxonomyTypes::ASSETS)
                                    ->get();
                                $tabs = [];

                                foreach ($nonWeightedTaxonomies as $taxonomy) {
                                    $tabs[] = Tabs\Tab::make($taxonomy->name)
                                        ->schema([
                                            Select::make("taxonomy_tags.{$taxonomy->id}")
                                                ->label("Select tag for {$taxonomy->name}")
                                                ->options(function () use ($taxonomy) {
                                                    return $taxonomy->tags()->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->multiple(false)
                                                ->helperText("Choose a tag from {$taxonomy->name} taxonomy"),
                                        ]);
                                }

                                return [Tabs::make()->tabs($tabs)];
                            })->columns(1),

                        // Weighted taxonomies - numeric inputs for each tag
                        Fieldset::make(__('Weighted Taxonomies'))
                            ->schema(function () {
                                $weightedTaxonomies = Taxonomy::where('weighted', true)
                                    ->where('type', TaxonomyTypes::ASSETS)
                                    ->get();
                                $tabs = [];

                                foreach ($weightedTaxonomies as $taxonomy) {
                                    $tagInputs = [];
                                    foreach ($taxonomy->tags as $tag) {
                                        $tagInputs[] = TextInput::make("weighted_tags.{$tag->id}")
                                            ->label($tag->name)
                                            ->numeric()
                                            ->step(0.000001)
                                            ->helperText("Weight for {$tag->name} tag");
                                    }

                                    if (!empty($tagInputs)) {
                                        $tabs[] = Tabs\Tab::make($taxonomy->name)
                                            ->schema($tagInputs);
                                    }
                                }

                                return [Tabs::make()->tabs($tabs)];
                            })->columns(1),
                    ])->columns(1)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        // Get all asset taxonomies for dynamic columns
        $assetTaxonomies = Taxonomy::where('type', TaxonomyTypes::ASSETS)->get();

        $columns = [
            TextColumn::make('name')
                ->searchable(),
            TextColumn::make('envelop.name')
                ->numeric()
                ->sortable(),
            TextColumn::make('class.name')
                ->numeric()
                ->sortable(),
            TextColumn::make('quantity')
                ->numeric()
                ->sortable(),
            TextColumn::make('cotation.name')
                ->numeric()
                ->sortable(),
            TextColumn::make('value')
                ->numeric()
                ->sortable()
                ->summarize(Sum::make('value')),
            TextColumn::make('update_method')
                ->label(__('Transfer Method'))
                ->badge()
                ->color('info')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('schedules.name')
                ->label('Schedules')
                ->badge()
                ->separator(', ')
                ->toggleable(isToggledHiddenByDefault: true)
                ->color('success'),
        ];

        // Add dynamic columns for each asset taxonomy
        foreach ($assetTaxonomies as $taxonomy) {
            $columns[] = TextColumn::make("taxonomy_tags.{$taxonomy->id}")
                ->label($taxonomy->name)
                ->badge()
                ->separator(', ')
                ->color('gray')
                ->getStateUsing(function ($record) use ($taxonomy) {
                    $tags = $record->tags()
                        ->where('taxonomy_id', $taxonomy->id)
                        ->pluck('name')
                        ->toArray();
                    return $tags;
                })
                ->toggleable(isToggledHiddenByDefault: false);
        }

        // Build filters array
        $filters = [
            // Class filter
            SelectFilter::make('class_id')
                ->label('Class')
                ->relationship('class', 'name')
                ->searchable()
                ->preload(),

            // Envelop filter
            SelectFilter::make('envelop_id')
                ->label('Envelop')
                ->relationship('envelop', 'name')
                ->searchable()
                ->preload(),
        ];

        // Add filters for each taxonomy
        foreach ($assetTaxonomies as $taxonomy) {
            $filters[] = SelectFilter::make("taxonomy_{$taxonomy->id}")
                ->label($taxonomy->name)
                ->options(function () use ($taxonomy) {
                    return $taxonomy->tags()->pluck('name', 'id');
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (!$data['value']) {
                        return $query;
                    }

                    return $query->whereHas('tags', function (Builder $query) use ($data) {
                        $query->where('taxonomy_tags.id', $data['value']);
                    });
                })
                ->searchable()
                ->preload();
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->defaultPaginationPageOption(100)
            ->paginated([50, 100, 'all'])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssets::route('/'),
            'create' => CreateAsset::route('/create'),
            'edit' => EditAsset::route('/{record}/edit'),
        ];
    }
}
