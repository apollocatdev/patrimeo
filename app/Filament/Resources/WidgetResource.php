<?php

namespace App\Filament\Resources;

use App\Models\Widget;
use Filament\Tables\Table;
use App\Charts\AbstractStat;
use Filament\Schemas\Schema;
use App\Charts\AbstractChart;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Enums\Widgets\WidgetType;
use App\Models\Filter as FilterModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\WidgetResource\Pages\EditWidget;
use App\Filament\Resources\WidgetResource\Pages\ListWidgets;
use App\Filament\Resources\WidgetResource\Pages\CreateWidget;

class WidgetResource extends Resource
{
    protected static ?string $model = Widget::class;

    protected static string | \BackedEnum | null $navigationIcon = 'bxs-widget';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->nullable(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options(WidgetType::class)
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn(Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicWidgetFields')
                        ->getChildSchema()
                        ->fill()),


                // Filters Section
                Select::make('filters')
                    ->label(__('Filters'))
                    ->multiple()
                    ->preload()
                    ->relationship('filters', 'name')
                    ->options(function () {
                        return FilterModel::where('user_id', Auth::user()->id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder(__('Select filters...')),

                // Dynamic Chart Fields
                Section::make('Widget Configuration')
                    ->schema(function (Get $get) {
                        $type = $get('type');
                        if (!$type) {
                            return [];
                        }

                        $widgetClass = $type->getClass();
                        return self::getDynamicWidgetFields($widgetClass);
                    })
                    ->statePath('parameters')
                    ->columns(2)
                    ->key('dynamicWidgetFields'),


            ])->columns(1);
    }

    protected static function getDynamicWidgetFields(string $widgetClass): array
    {
        if (!class_exists($widgetClass)) {
            return [];
        }

        $reflection = new \ReflectionClass($widgetClass);
        if (!$reflection->isSubclassOf(AbstractChart::class) && !$reflection->isSubclassOf(AbstractStat::class)) {
            return [];
        }

        if (method_exists($widgetClass, 'form')) {
            return $widgetClass::form();
        }

        return [];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('type'),
                TextColumn::make('category')
                    ->label(__('Category'))
                    ->state(fn($record) => str_starts_with($record->type->value, 'stat_') ? 'Stat' : 'Chart'),
                TextColumn::make('description'),
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
            'index' => ListWidgets::route('/'),
            'create' => CreateWidget::route('/create'),
            'edit' => EditWidget::route('/{record}/edit'),
        ];
    }
}
