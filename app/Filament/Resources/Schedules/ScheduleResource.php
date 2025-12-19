<?php

namespace App\Filament\Resources\Schedules;

use BackedEnum;
use App\Models\Schedule;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\ScheduleAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Resources\Schedules\Pages\CreateSchedule;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('cron')
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('Cron expression (e.g., "0 9 * * *" for daily at 9 AM)')),

                ToggleButtons::make('cron_preset')
                    ->label(__('Quick Presets'))
                    ->options([
                        '0 * * * *' => __('Hourly'),
                        '0 9 * * *' => __('Daily (9 AM)'),
                        '0 9 * * 1' => __('Weekly (Mon 9 AM)'),
                        '0 9 1 * *' => __('Monthly (1st 9 AM)'),
                        '0 9 1 1 *' => __('Yearly (Jan 1st 9 AM)'),
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('cron', $state);
                        }
                    })
                    ->helperText(__('Select a preset to auto-fill the cron expression'))
                    ->inline()
                    ->multiple(false),


                Repeater::make('actions')
                    ->schema([
                        Select::make('action')
                            ->options(ScheduleAction::class)
                            ->live()
                            ->required(),

                        TextInput::make('message')
                            ->hidden(fn(Get $get): bool => $get('action') === ScheduleAction::UPDATE)
                            ->label(__('Custom Message'))
                            ->placeholder(__('It\'s time to update'))
                            ->helperText(__('Leave empty to use default message')),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel(__('Add Action'))
                    ->collapsible()
                    ->mutateDehydratedStateUsing(function ($state) {
                        // Convert Actions object to array for form display
                        if ($state instanceof \App\Data\Schedules\Actions) {
                            return $state->toArray();
                        }
                        return $state;
                    })
                    ->afterStateHydrated(function ($component, $state) {
                        // Convert Actions object to array when loading the form
                        if ($state instanceof \App\Data\Schedules\Actions) {
                            $component->state($state->toArray());
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        // Convert array to Actions object when saving
                        if (is_array($state)) {
                            return \App\Data\Schedules\Actions::fromFormArray($state);
                        }
                        return $state;
                    }),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cron')
                    ->label(__('Cron Expression'))
                    ->searchable(),

                TextColumn::make('actions')
                    ->label(__('Actions'))
                    ->formatStateUsing(function ($state) {
                        return collect($state)->pluck('action')->join(', ');
                    }),

                TextColumn::make('valuations_count')
                    ->label(__('Valuations'))
                    ->counts('valuations'),

                TextColumn::make('assets_count')
                    ->label(__('Assets'))
                    ->counts('assets'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
