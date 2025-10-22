<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AssetClassResource\Pages\ListAssetClasses;
use App\Filament\Resources\AssetClassResource\Pages\CreateAssetClass;
use App\Filament\Resources\AssetClassResource\Pages\EditAssetClass;
use App\Filament\Resources\AssetClassResource\Pages;
use App\Filament\Resources\AssetClassResource\RelationManagers;
use App\Models\AssetClass;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetClassResource extends Resource
{
    protected static ?string $model = AssetClass::class;

    protected static string | \BackedEnum | null $navigationIcon = 'carbon-data-categorical';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
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
            'index' => ListAssetClasses::route('/'),
            'create' => CreateAssetClass::route('/create'),
            'edit' => EditAssetClass::route('/{record}/edit'),
        ];
    }
}
