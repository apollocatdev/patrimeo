<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DashboardResource\Pages\ListDashboards;
use App\Filament\Resources\DashboardResource\Pages\CreateDashboard;
use App\Filament\Resources\DashboardResource\Pages\EditDashboard;
use App\Filament\Resources\DashboardResource\Pages;
use App\Filament\Resources\DashboardResource\RelationManagers;
use App\Models\Dashboard;
use App\Models\Widget;
use App\Enums\Widgets\WidgetType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static string | \BackedEnum | null $navigationIcon = 'ri-dashboard-3-line';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('navigation_title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('n_columns')
                    ->label('Number of columns')
                    ->numeric()
                    ->default(4)
                    ->minValue(1)
                    ->maxValue(12)
                    ->required(),

                Repeater::make('stats_widgets')
                    ->label('Stats Widgets')
                    ->schema([
                        Select::make('widget_id')
                            ->label('Widget')
                            ->options(function () {
                                return Widget::whereIn('type', [
                                    WidgetType::STAT_NUMBER_OF_ENTITIES,
                                    WidgetType::STAT_PORTFOLIO_VALUE,
                                    WidgetType::STAT_PORTFOLIO_GAIN,
                                    WidgetType::STAT_PORTFOLIO_PERFORMANCE,
                                ])->pluck('title', 'id');
                            })
                            ->required()
                            ->searchable(),
                    ])
                    ->reorderable()
                    ->addActionLabel('Add Stats Widget')
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => Widget::find($state['widget_id'])?->title ?? 'New Stats Widget'),

                Repeater::make('chart_widgets')
                    ->label('Chart Widgets')
                    ->schema([
                        Select::make('widget_id')
                            ->label('Widget')
                            ->options(function () {
                                return Widget::whereIn('type', [
                                    WidgetType::CHART_LINE_VALUE_EVOLUTION,
                                    WidgetType::CHART_DONUT_ASSETS_DISTRIBUTION,
                                    WidgetType::CHART_TREEMAP_ASSETS_DISTRIBUTION,
                                    WidgetType::CHART_BAR_PERFORMANCE_EVOLUTION,
                                ])->pluck('title', 'id');
                            })
                            ->required()
                            ->searchable(),
                        Select::make('column_span')
                            ->label('Column Span')
                            ->options([
                                '1' => '1 column',
                                '2' => '2 columns',
                                '3' => '3 columns',
                                '4' => '4 columns',
                                'full' => 'Full width',
                            ])
                            ->default('2')
                            ->required(),
                    ])
                    ->reorderable()
                    ->addActionLabel('Add Chart Widget')
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => Widget::find($state['widget_id'])?->title ?? 'New Chart Widget'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('navigation_title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('n_columns')
                    ->label('Columns')
                    ->sortable(),
                TextColumn::make('widgets_count')
                    ->label('Widgets')
                    ->counts('widgets')
                    ->sortable(),
                IconColumn::make('default')
                    ->boolean()
                    ->label('Default')
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
            'index' => ListDashboards::route('/'),
            'create' => CreateDashboard::route('/create'),
            'edit' => EditDashboard::route('/{record}/edit'),
        ];
    }
}
