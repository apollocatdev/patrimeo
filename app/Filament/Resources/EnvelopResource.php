<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\EnvelopResource\Pages\ListEnvelops;
use App\Filament\Resources\EnvelopResource\Pages\CreateEnvelop;
use App\Filament\Resources\EnvelopResource\Pages\EditEnvelop;
use App\Filament\Resources\EnvelopResource\Pages;
use App\Filament\Resources\EnvelopResource\RelationManagers;
use App\Models\Envelop;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnvelopResource extends Resource
{
    protected static ?string $model = Envelop::class;

    protected static string | \BackedEnum | null $navigationIcon = 'uni-envelopes-o';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type_id')
                    ->relationship('type', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('all')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type.name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            'index' => ListEnvelops::route('/'),
            'create' => CreateEnvelop::route('/create'),
            'edit' => EditEnvelop::route('/{record}/edit'),
        ];
    }
}
