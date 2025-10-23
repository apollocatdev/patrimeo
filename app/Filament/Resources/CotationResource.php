<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cotation;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use App\Enums\CotationUpdateMethod;
use App\Exceptions\CotationException;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\CotationResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CotationResource\RelationManagers;
use App\Filament\Resources\CotationResource\Pages\EditCotation;
use App\Filament\Resources\CotationResource\Pages\ListCotations;
use App\Filament\Resources\CotationResource\Pages\CreateCotation;
use App\Jobs\SyncCotations;
use Filament\Notifications\Notification;

class CotationResource extends Resource
{
    protected static ?string $model = Cotation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string | \UnitEnum | null $navigationGroup = 'Cotations';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('isin'),
                Select::make('currency_id')
                    ->relationship('currency', 'symbol')
                    ->required(),
                TextInput::make('value')
                    ->numeric(),
                Select::make('update_method')
                    ->options(CotationUpdateMethod::class)
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn(Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicCotationFields')
                        ->getChildSchema()
                        ->fill()),

                Section::make(__('Update parameters'))
                    ->schema(function (Get $get): array {
                        if (empty($get('update_method'))) {
                            return [];
                        }

                        $serviceClass = $get('update_method')->getServiceClass();

                        // If no service class (like FIXED or MANUAL), don't show dynamic fields
                        if (!$serviceClass) {
                            return [];
                        }

                        return [
                            Group::make()->schema($serviceClass::getFields())->statePath('update_data'),
                            Textarea::make('test_results')
                                ->label('Test Results')
                                ->rows(5)
                                ->disabled(),
                            Action::make('test')
                                ->label('Test')
                                ->action(function (Set $set) use ($get) {
                                    $serviceClass = $get('update_method')->getServiceClass();
                                    $fields = $serviceClass::getFields();
                                    $updateData = [];
                                    foreach ($fields as $name => $field) {
                                        $updateData[$name] = $get('update_data')[$name];
                                    }
                                    $cotation = new Cotation(['update_data' => $updateData, 'update_method' => $get('update_method')]);
                                    $service = new $serviceClass($cotation);
                                    try {
                                        $result = $service->getQuote();
                                        $set('test_results', __('Price extracted: :price', ['price' => $result]));
                                    } catch (CotationException $e) {
                                        $set('test_results', $e->getFullMessage());
                                    }
                                })
                        ];
                    })->key('dynamicCotationFields'),

                Section::make(__('Schedules'))
                    ->description('Select schedules for automatic updates')
                    ->schema([
                        CheckboxList::make('schedules')
                            ->relationship('schedules', 'name')
                            ->descriptions(function ($record) {
                                return $record->cron;
                            })
                            ->helperText('Choose which schedules should update this cotation'),
                    ]),
            ])->columns(1);
    }





    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('isin')
                    ->searchable(),
                TextColumn::make('currency.symbol')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('value')
                    ->numeric(decimalPlaces: 3, locale: 'fr')
                    ->sortable(),
                TextColumn::make('value_main_currency')
                    ->numeric(decimalPlaces: 3, locale: 'fr')
                    ->sortable(),
                TextColumn::make('last_update')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('update_method')
                    ->searchable(),
                TextColumn::make('schedules.name')
                    ->label('Schedules')
                    ->badge()
                    ->separator(', ')
                    ->color('success'),
            ])
            ->defaultPaginationPageOption(100)
            ->paginated([50, 100, 'all'])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('refresh')
                    ->label(__('Update'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(
                        fn(Cotation $record): bool =>
                        !in_array($record->update_method, [CotationUpdateMethod::FIXED, CotationUpdateMethod::MANUAL])
                    )
                    ->action(function (Cotation $record) {
                        try {
                            // Get the service class for this cotation
                            $serviceClass = $record->update_method->getServiceClass();
                            if ($serviceClass) {
                                $service = new $serviceClass($record);
                                $newValue = $service->getQuote();

                                // Update the cotation value
                                $record->update([
                                    'value' => $newValue,
                                    'last_update' => now(),
                                ]);

                                // Create cotation update record
                                $record->updates()->create([
                                    'user_id' => $record->user_id,
                                    'date' => now(),
                                    'status' => 'success',
                                    'message' => null,
                                    'value' => $newValue,
                                ]);

                                Notification::make()
                                    ->title(__('Cotation updated successfully'))
                                    ->body(__('New value: :value', ['value' => $newValue]))
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            // Create cotation update record for failed update
                            $record->updates()->create([
                                'user_id' => $record->user_id,
                                'date' => now(),
                                'status' => 'error',
                                'message' => $e->getMessage(),
                                'value' => null,
                            ]);

                            Notification::make()
                                ->title(__('Cotation update failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListCotations::route('/'),
            'create' => CreateCotation::route('/create'),
            'edit' => EditCotation::route('/{record}/edit'),
        ];
    }
}
