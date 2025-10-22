<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Transfer;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TransferType;
use App\Enums\TaxonomyTypes;
use App\Models\Taxonomy;
use App\Filament\Resources\TransferResource\Pages;
use App\Filament\Resources\TransferResource\Pages\EditTransfer;
use App\Filament\Resources\TransferResource\Pages\ListTransfers;
use App\Filament\Resources\TransferResource\Pages\CreateTransfer;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-arrow-up-tray';
    protected static string | \UnitEnum | null $navigationGroup = 'Portfolio';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(TransferType::class)
                    ->required(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()->toDateString()),
                Section::make('Source')
                    ->schema([
                        Select::make('source_id')
                            ->label('From')
                            ->relationship('source', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('source_quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->step(0.000001),
                    ]),
                Section::make('Destination')
                    ->schema([
                        Select::make('destination_id')
                            ->label('To')
                            ->relationship('destination', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('destination_quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->step(0.000001),
                    ]),
                Textarea::make('comment')
                    ->rows(3),
                Section::make('Tags')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        CheckboxList::make('tags')
                            ->relationship('tags', 'name')
                            ->options(function () {
                                return Taxonomy::where('type', TaxonomyTypes::TRANSFERS)
                                    ->with('tags')
                                    ->get()
                                    ->flatMap(function ($taxonomy) {
                                        return $taxonomy->tags->mapWithKeys(function ($tag) use ($taxonomy) {
                                            return [$tag->id => "{$taxonomy->name}: {$tag->name}"];
                                        });
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Select tags to categorize this transfer'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(TransferType $state): string => match ($state) {
                        TransferType::Expense => 'danger',
                        TransferType::Transfer => 'info',
                        TransferType::Income => 'success',
                    }),
                TextColumn::make('source.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('destination.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->separator(', ')
                    ->color('info'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->defaultPaginationPageOption(50)
            ->paginated([25, 50, 100, 'all'])
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
            'index' => ListTransfers::route('/'),
            'create' => CreateTransfer::route('/create'),
            'edit' => EditTransfer::route('/{record}/edit'),
        ];
    }
}
