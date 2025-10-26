<?php

namespace App\Filament\Resources\TaxonomyResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\TaxonomyTag;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use App\Enums\TaxonomyTypes;
use Illuminate\Support\Facades\Auth;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = TaxonomyTag::class;


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->toggleable(false),
                TextColumn::make('assets_count')
                    ->label('Assets Count')
                    ->counts('assets')
                    ->sortable()
                    ->toggleable(false),
                TextColumn::make('transactions_count')
                    ->label('Transactions Count')
                    ->counts('transactions')
                    ->sortable()
                    ->toggleable(false),
            ])
            ->searchable(false)
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        CheckboxList::make('assets')
                            ->relationship('assets', 'name')
                            ->visible(function () {
                                $taxonomy = $this->getOwnerRecord();
                                return $taxonomy && $taxonomy->type === TaxonomyTypes::ASSETS && !$taxonomy->weighted;
                            })
                            ->helperText('Select assets to associate with this tag (only for non-weighted taxonomies)')
                            ->searchable(),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::user()->id;
                        $data['taxonomy_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        CheckboxList::make('assets')
                            ->relationship('assets', 'name')
                            ->visible(function () {
                                $taxonomy = $this->getOwnerRecord();
                                return $taxonomy && $taxonomy->type === TaxonomyTypes::ASSETS && !$taxonomy->weighted;
                            })
                            ->helperText('Select assets to associate with this tag (only for non-weighted taxonomies)')
                            ->searchable(),
                    ]),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}
