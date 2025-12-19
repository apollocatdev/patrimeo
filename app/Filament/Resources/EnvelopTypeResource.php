<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\EnvelopTypeResource\Pages\ListEnvelopTypes;
use App\Filament\Resources\EnvelopTypeResource\Pages\CreateEnvelopType;
use App\Filament\Resources\EnvelopTypeResource\Pages\EditEnvelopType;
use App\Filament\Resources\EnvelopTypeResource\Pages;
use App\Filament\Resources\EnvelopTypeResource\RelationManagers;
use App\Models\EnvelopType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnvelopTypeResource extends Resource
{
    protected static ?string $model = EnvelopType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'uni-envelope-question-o';
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
            'index' => ListEnvelopTypes::route('/'),
            'create' => CreateEnvelopType::route('/create'),
            'edit' => EditEnvelopType::route('/{record}/edit'),
        ];
    }
}
