<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\TaxonomyTag;
use App\Enums\TaxonomyTypes;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Tables\Grouping\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\TaxonomyTagResource\Pages;
use Filament\Forms\Components\TextInput as FormsTextInput;

class TaxonomyTagResource extends Resource
{
    protected static ?string $model = TaxonomyTag::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('taxonomy_id')
                    ->relationship('taxonomy', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),

                // Only show asset selection for non-weighted taxonomies
                CheckboxList::make('assets')
                    ->relationship('assets', 'name')
                    ->visible(function ($get) {
                        $taxonomyId = $get('taxonomy_id');
                        if (!$taxonomyId) return false;
                        $taxonomy = \App\Models\Taxonomy::find($taxonomyId);
                        return $taxonomy && $taxonomy->type === TaxonomyTypes::ASSETS && !$taxonomy->weighted;
                    })
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
                TextColumn::make('taxonomy.type')
                    ->label('Taxonomy type')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
                TextColumn::make('taxonomy.name')
                    ->label('Taxonomy')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
                TextColumn::make('taxonomy.weighted')
                    ->label('Weighted')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'warning' : 'success')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Yes' : 'No'),
                TextColumn::make('assets_count')
                    ->label('Assets Count')
                    ->counts('assets')
                    ->sortable(),
                TextColumn::make('transactions_count')
                    ->label('Transactions Count')
                    ->counts('transactions')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([Group::make('taxonomy.name')->label('Taxonomy')])
            ->defaultGroup('taxonomy.name')
            ->filters([
                Tables\Filters\SelectFilter::make('taxonomy')
                    ->relationship('taxonomy', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('weighted')
                    ->label('Weighted Taxonomy')
                    ->boolean()
                    ->trueLabel('Weighted only')
                    ->falseLabel('Non-weighted only')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('taxonomy', function (Builder $query) use ($data) {
                            $query->where('taxonomies.weighted', $data['value'] ?? false);
                        });
                    })
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('taxonomy.name');
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
