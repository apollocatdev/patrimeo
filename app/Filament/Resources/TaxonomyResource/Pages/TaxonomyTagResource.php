<?php

namespace App\Filament\Resources\TaxonomyResource\Pages;

use Filament\Forms;
use Filament\Tables;
use App\Models\TaxonomyTag;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TaxonomyResource\Pages\TaxonomyTagResource\Pages;

class TaxonomyTagResource extends Resource
{
    protected static ?string $model = TaxonomyTag::class;

    protected static ?string $parentResource = \App\Filament\Resources\TaxonomyResource::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                // Only show asset selection for non-weighted taxonomies
                CheckboxList::make('assets')
                    ->relationship('assets', 'name')
                    ->visible(fn($livewire) => !$livewire->ownerRecord->taxonomy->weighted)
                    ->helperText('Select assets to associate with this tag (only for non-weighted taxonomies)')
                    ->searchable(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assets_count')
                    ->label('Assets Count')
                    ->counts('assets')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxonomyTags::route('/'),
            'create' => Pages\CreateTaxonomyTag::route('/create'),
            'edit' => Pages\EditTaxonomyTag::route('/{record}/edit'),
        ];
    }
}
