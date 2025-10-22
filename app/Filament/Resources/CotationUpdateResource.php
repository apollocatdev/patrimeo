<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\CotationUpdate;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CotationUpdateResource\Pages;
use App\Filament\Resources\CotationUpdateResource\RelationManagers;
use App\Filament\Resources\CotationUpdateResource\Pages\ViewCotationUpdate;
use App\Filament\Resources\CotationUpdateResource\Pages\ListCotationUpdates;

class CotationUpdateResource extends Resource
{
    protected static ?string $model = CotationUpdate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'zondicon-refresh';
    protected static string | \UnitEnum | null $navigationGroup = 'Cotations';
    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Infolists\Components\TextEntry::make('date')
                    ->label(__('Date')),
                \Filament\Infolists\Components\TextEntry::make('cotation.name')
                    ->label(__('Cotation')),
                \Filament\Infolists\Components\TextEntry::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                \Filament\Infolists\Components\TextEntry::make('message')
                    ->label(__('Message')),
                \Filament\Infolists\Components\TextEntry::make('http_status_code')
                    ->label(__('HTTP Status Code')),
                \Filament\Infolists\Components\TextEntry::make('error_details')
                    ->label(__('Error Details')),
                \Filament\Infolists\Components\TextEntry::make('value')
                    ->label(__('Value')),
            ])->columns(1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('cotation.name')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('success')
                    ->required(),
                Forms\Components\TextInput::make('message'),
                Forms\Components\TextInput::make('http_status_code'),
                Forms\Components\Textarea::make('error_details'),
                Forms\Components\TextInput::make('value')
                    ->numeric(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('message')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('cotation.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cotation.update_method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'error' => 'Error',
                        'pending' => 'Pending',
                    ])
                // ->default('error')
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
                ViewAction::make(),
            ])
            ->defaultSort('date', 'desc')
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
            'index' => ListCotationUpdates::route('/'),
            'view' => ViewCotationUpdate::route('/{record}'),
            // 'create' => Pages\CreateCotationUpdate::route('/create'),
            // 'edit' => Pages\EditCotationUpdate::route('/{record}/edit'),
        ];
    }
}
