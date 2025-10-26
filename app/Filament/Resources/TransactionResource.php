<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Taxonomy;
use App\Models\Transaction;
use Filament\Tables\Table;
use App\Enums\TransactionType;
use App\Enums\TaxonomyTypes;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-arrow-up-tray';
    protected static string | \UnitEnum | null $navigationGroup = 'Portfolio';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(TransactionType::class)
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
                Toggle::make('reconciled')
                    ->label('Reconciled')
                    ->helperText('Mark this transaction as reconciled'),
                Section::make('Tags')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        CheckboxList::make('tags')
                            ->relationship('tags', 'name')
                            ->options(function () {
                                return Taxonomy::where('type', TaxonomyTypes::TRANSACTIONS)
                                    ->with('tags')
                                    ->get()
                                    ->flatMap(function ($taxonomy) {
                                        return $taxonomy->tags->mapWithKeys(function ($tag) use ($taxonomy) {
                                            return [$tag->id => "{$taxonomy->name}: {$tag->name}"];
                                        });
                                    });
                            })
                            ->searchable()
                            ->helperText('Select tags to categorize this transaction'),
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
                IconColumn::make('reconciled')
                    ->label('Reconciled')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(TransactionType $state): string => match ($state) {
                        TransactionType::Expense => 'danger',
                        TransactionType::Transfer => 'info',
                        TransactionType::Income => 'success',
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
                TextColumn::make('comment')
                    ->limit(50),
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
                DeleteAction::make()
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
