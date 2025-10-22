<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\CotationHistory;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use App\Filament\Resources\CotationHistoryResource\Pages;

class CotationHistoryResource extends Resource
{
    protected static ?string $model = CotationHistory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | \UnitEnum | null $navigationGroup = 'Cotations';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cotation_id')
                    ->relationship('cotation', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('value')
                    ->numeric()
                    ->required(),
                TextInput::make('value_main_currency')
                    ->numeric()
                    ->required(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cotation.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->numeric(decimalPlaces: 3, locale: 'fr')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value_main_currency')
                    ->numeric(decimalPlaces: 3, locale: 'fr')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(100)
            ->paginated([50, 100, 'all'])
            ->filters([
                SelectFilter::make('cotation')
                    ->relationship('cotation', 'name')
                    ->searchable()
                    ->label('Filter by Cotation'),
            ])
            ->recordActions([
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
            'index' => Pages\ListCotationHistories::route('/'),
            'create' => Pages\CreateCotationHistory::route('/create'),
            'edit' => Pages\EditCotationHistory::route('/{record}/edit'),
        ];
    }
}
