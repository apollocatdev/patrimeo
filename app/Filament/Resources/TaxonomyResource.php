<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Taxonomy;
use Filament\Tables\Table;
use App\Enums\TaxonomyTypes;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TaxonomyResource\Pages;
use App\Filament\Resources\TaxonomyResource\RelationManagers;
use App\Filament\Resources\TaxonomyResource\RelationManagers\TagsRelationManager;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(TaxonomyTypes::class)
                    ->required()
                    ->default(TaxonomyTypes::ASSETS)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Automatically set weighted to false for transactions
                        if ($state === TaxonomyTypes::TRANSACTIONS) {
                            $set('weighted', false);
                        }
                    }),
                Toggle::make('weighted')
                    ->label('Weighted Taxonomy')
                    ->helperText('Weighted taxonomies allow numeric values for tags, while non-weighted taxonomies are simple associations. Transactions taxonomies are automatically unweighted.')
                    ->default(false)
                    ->hidden(fn(callable $get) => $get('type') === TaxonomyTypes::TRANSACTIONS),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name')
                    ->size('lg')
                    ->weight('bold'),
                TextEntry::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(TaxonomyTypes $state): string => match ($state) {
                        TaxonomyTypes::ASSETS => 'success',
                        TaxonomyTypes::TRANSACTIONS => 'info',
                    }),
                IconEntry::make('weighted')
                    ->label('Weighted Taxonomy')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(TaxonomyTypes $state): string => match ($state) {
                        TaxonomyTypes::ASSETS => 'success',
                        TaxonomyTypes::TRANSACTIONS => 'info',
                    })
                    ->sortable(),
                IconColumn::make('weighted')
                    ->label('Weighted')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('tags_count')
                    ->label('Tags Count')
                    ->counts('tags')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('weighted')
                    ->label('Weighted Taxonomy')
                    ->boolean()
                    ->trueLabel('Weighted only')
                    ->falseLabel('Non-weighted only')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('manage')
                    ->label('Manage')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->url(fn(Taxonomy $record): string => static::getUrl('view', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxonomies::route('/'),
            'create' => Pages\CreateTaxonomy::route('/create'),
            'edit' => Pages\EditTaxonomy::route('/{record}/edit'),
            'view' => Pages\ViewTaxonomy::route('/{record}'),
        ];
    }
}
