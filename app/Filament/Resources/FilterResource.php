<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Filter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Data\Filters\Filters;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use App\Enums\Filters\FilterEntity;
use App\Enums\Filters\FilterOperator;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Enums\Filters\FilterLogicOperator;
use App\Enums\Filters\FilterRuleAssetType;
use App\Enums\Filters\FilterRuleValuationType;
use App\Enums\Filters\FilterRuleOperator;
use App\Filament\Resources\FilterResource\Pages;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-funnel';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Select::make('entity')
                    ->label(__('Entity'))
                    ->options(FilterEntity::class)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn() => 'filters'),
                Select::make('operation')
                    ->label(__('Operation'))
                    ->options(FilterLogicOperator::class)
                    ->required()
                    ->default(FilterLogicOperator::AND),
                Repeater::make('filters')
                    ->label(__('Filters'))
                    ->afterStateHydrated(function (Repeater $component, $state) {
                        if ($state instanceof Filters) {
                            $component->state($state->toArray());
                        }
                    })
                    ->dehydrateStateUsing(function (array $state, callable $get): Filters {
                        $entity = $get('entity');
                        return Filters::fromFormArray($state, $entity);
                    })
                    ->schema([
                        Select::make('type')
                            ->label(__('Filter Type'))
                            ->options(function (callable $get) {
                                $entity = $get('../../entity');
                                if ($entity === FilterEntity::ASSETS) {
                                    return FilterRuleAssetType::class;
                                }
                                if ($entity === FilterEntity::VALUATIONS) {
                                    return FilterRuleValuationType::class;
                                }
                                return [];
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('values', '');
                            }),
                        Select::make('operator')
                            ->label(__('Operator'))
                            ->options(FilterRuleOperator::class)
                            ->visible(function (callable $get) {
                                $entity = $get('../../entity');
                                $type = $get('type');

                                if ($entity === FilterEntity::ASSETS && $type) {
                                    $enumType = FilterRuleAssetType::tryFrom($type);
                                    return $enumType && $enumType->isNumericRule();
                                }

                                if ($entity === FilterEntity::VALUATIONS && $type) {
                                    $enumType = FilterRuleValuationType::tryFrom($type);
                                    return $enumType && $enumType->isNumericRule();
                                }

                                return false;
                            })
                            ->required(fn(callable $get) => $get('operator') !== null),
                        TextInput::make('values')
                            ->label(function (callable $get) {
                                $entity = $get('../../entity');
                                $type = $get('type');

                                if ($entity === FilterEntity::ASSETS && $type) {
                                    $enumType = FilterRuleAssetType::tryFrom($type);
                                    if ($enumType && $enumType->isNumericRule()) {
                                        return __('Value');
                                    }
                                }

                                if ($entity === FilterEntity::VALUATIONS && $type) {
                                    $enumType = FilterRuleValuationType::tryFrom($type);
                                    if ($enumType && $enumType->isNumericRule()) {
                                        return __('Value');
                                    }
                                }

                                return __('Values (comma separated)');
                            })
                            ->required()
                            ->helperText(function (callable $get) {
                                $entity = $get('../../entity');
                                $type = $get('type');

                                if ($entity === FilterEntity::ASSETS && $type) {
                                    $enumType = FilterRuleAssetType::tryFrom($type);
                                    if ($enumType && $enumType->isNumericRule()) {
                                        return __('Enter a single numeric value');
                                    }
                                }

                                if ($entity === FilterEntity::VALUATIONS && $type) {
                                    $enumType = FilterRuleValuationType::tryFrom($type);
                                    if ($enumType && $enumType->isNumericRule()) {
                                        return __('Enter a single numeric value');
                                    }
                                }

                                return __('Enter values separated by commas');
                            }),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->visible(fn(callable $get) => $get('entity') !== null),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entity')
                    ->label(__('Entity'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('operation')
                    ->label(__('Operation'))
                    ->badge()
                    ->color('warning'),
                TextColumn::make('filters')
                    ->label(__('Filters Count'))
                    ->formatStateUsing(fn(Filters $state): string => count($state->rules))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListFilters::route('/'),
            'create' => Pages\CreateFilter::route('/create'),
            'edit' => Pages\EditFilter::route('/{record}/edit'),
        ];
    }
}
