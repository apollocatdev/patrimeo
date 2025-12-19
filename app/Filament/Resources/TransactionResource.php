<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Livewire\Component;
use App\Models\Taxonomy;
use Filament\Tables\Table;
use App\Models\Transaction;
use App\Enums\TaxonomyTypes;
use Filament\Schemas\Schema;
use App\Enums\TransactionType;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\TransactionResource\Pages;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
                ...Taxonomy::where('type', TaxonomyTypes::TRANSACTIONS)
                    ->with('tags')
                    ->get()
                    ->map(function ($taxonomy) {
                        return Section::make(__('Taxonomy:') . ' ' . $taxonomy->name)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                CheckboxList::make('tags')
                                    ->relationship(
                                        'tags',
                                        'name',
                                        modifyQueryUsing: fn($query) => $query->where('taxonomy_id', $taxonomy->id)
                                    )
                                    ->helperText("Select {$taxonomy->name} tags"),
                            ]);
                    })
                    ->toArray(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                IconColumn::make('reconciled')
                    ->label(__('Reconciled'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn(TransactionType $state): string => match ($state) {
                        TransactionType::Expense => 'danger',
                        TransactionType::Transfer => 'info',
                        TransactionType::Income => 'success',
                    }),
                TextColumn::make('simple_account_name')
                    ->label(__('Account'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'simple'),
                TextColumn::make('simple_amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->weight(FontWeight::Bold)
                    ->color(fn(float $state): string => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'gray'))
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'simple'),
                TextColumn::make('source.name')
                    ->label(__('Source'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'source-destination'),
                TextColumn::make('source_quantity')
                    ->label(__('Source Quantity'))
                    ->numeric()
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'source-destination'),
                TextColumn::make('destination.name')
                    ->label(__('Destination'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'source-destination'),
                TextColumn::make('destination_quantity')
                    ->label(__('Destination Quantity'))
                    ->numeric()
                    ->sortable()
                    ->visible(fn(Component $livewire): bool => $livewire->activeTab === 'source-destination'),
                TextColumn::make('comment')
                    ->label(__('Comment'))
                    ->limit(50),
                TextColumn::make('tags.name')
                    ->label(__('Tags'))
                    ->badge()
                    ->separator(', ')
                    ->color('info'),
            ])
            // ->columns([
            //     TextColumn::make('date')
            //         ->date()
            //         ->sortable(),
            //     IconColumn::make('reconciled')
            //         ->label('Reconciled')
            //         ->boolean()
            //         ->sortable(),
            //     TextColumn::make('type')
            //         ->badge()
            //         ->color(fn(TransactionType $state): string => match ($state) {
            //             TransactionType::Expense => 'danger',
            //             TransactionType::Transfer => 'info',
            //             TransactionType::Income => 'success',
            //         }),
            //     TextColumn::make('source.name')
            //         ->searchable()
            //         ->sortable(),
            //     TextColumn::make('source_quantity')
            //         ->numeric()
            //         ->sortable(),
            //     TextColumn::make('destination.name')
            //         ->searchable()
            //         ->sortable(),
            //     TextColumn::make('destination_quantity')
            //         ->numeric()
            //         ->sortable(),
            //     TextColumn::make('comment')
            //         ->limit(50),
            //     TextColumn::make('tags.name')
            //         ->label('Tags')
            //         ->badge()
            //         ->separator(', ')
            //         ->color('info'),
            // ])
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
                    BulkAction::make('toggleReconciled')
                        ->label(__('Update reconciliation status'))
                        ->icon('heroicon-m-check-badge')
                        ->schema([
                            ToggleButtons::make('reconciled')
                                ->label(__('State'))
                                ->options([
                                    '1' => __('Reconciled'),
                                    '0' => __('Not reconciled'),
                                ])
                                ->inline()
                                ->required()
                                ->default('1'),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->action(function (EloquentCollection $records, array $data): void {
                            $reconciled = (bool) ((int) ($data['reconciled'] ?? 0));

                            foreach ($records as $record) {
                                $record->update(['reconciled' => $reconciled]);
                            }
                        }),
                    BulkAction::make('tagTransactions')
                        ->label(__('Tag transactions'))
                        ->icon('heroicon-m-tag')
                        ->visible(fn(): bool => Taxonomy::where('type', TaxonomyTypes::TRANSACTIONS)->exists())
                        ->schema(function (): array {
                            return Taxonomy::where('type', TaxonomyTypes::TRANSACTIONS)
                                ->with('tags')
                                ->get()
                                ->map(function (Taxonomy $taxonomy) {
                                    return Section::make($taxonomy->name)
                                        ->schema([
                                            CheckboxList::make("taxonomy_{$taxonomy->id}")
                                                ->label(__('Tags'))
                                                ->options(
                                                    $taxonomy->tags
                                                        ->sortBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray()
                                                )
                                                ->columns(2),
                                        ]);
                                })
                                ->toArray();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->action(function (EloquentCollection $records, array $data): void {
                            $tagIds = collect($data)
                                ->filter(fn($value) => is_array($value))
                                ->flatten()
                                ->unique()
                                ->filter()
                                ->values();

                            if ($tagIds->isEmpty()) {
                                return;
                            }

                            foreach ($records as $record) {
                                $record->tags()->syncWithoutDetaching($tagIds->all());
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getSimpleViewColumns(): array
    {
        return [
            TextColumn::make('date')
                ->label(__('Date'))
                ->date()
                ->sortable(),
            IconColumn::make('reconciled')
                ->label(__('Reconciled'))
                ->boolean()
                ->sortable(),
            TextColumn::make('type')
                ->label(__('Type'))
                ->badge()
                ->color(fn(TransactionType $state): string => match ($state) {
                    TransactionType::Expense => 'danger',
                    TransactionType::Transfer => 'info',
                    TransactionType::Income => 'success',
                }),
            TextColumn::make('simple_account_name')
                ->label(__('Account'))
                ->searchable()
                ->sortable(),
            TextColumn::make('simple_amount')
                ->label(__('Amount'))
                ->numeric()
                ->weight(FontWeight::Bold)
                ->color(fn(float $state): string => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'gray'))
                ->sortable(),
            TextColumn::make('comment')
                ->label(__('Comment'))
                ->limit(50),
            TextColumn::make('tags.name')
                ->label(__('Tags'))
                ->badge()
                ->separator(', ')
                ->color('info'),
        ];
    }
    public static function getSourceDestinationColumns(): array
    {
        return [

            TextColumn::make('date')
                ->label(__('Date'))
                ->date()
                ->sortable(),
            IconColumn::make('reconciled')
                ->label(__('Reconciled'))
                ->boolean()
                ->sortable(),
            TextColumn::make('type')
                ->label(__('Type'))
                ->badge()
                ->color(fn(TransactionType $state): string => match ($state) {
                    TransactionType::Expense => 'danger',
                    TransactionType::Transfer => 'info',
                    TransactionType::Income => 'success',
                }),
            TextColumn::make('source.name')
                ->label(__('Source'))
                ->searchable()
                ->sortable(),
            TextColumn::make('source_quantity')
                ->label(__('Source Quantity'))
                ->numeric()
                ->sortable(),
            TextColumn::make('destination.name')
                ->label(__('Destination'))
                ->searchable()
                ->sortable(),
            TextColumn::make('destination_quantity')
                ->label(__('Destination Quantity'))
                ->numeric()
                ->sortable(),
            TextColumn::make('comment')
                ->label(__('Comment'))
                ->limit(50),
            TextColumn::make('tags.name')
                ->label(__('Tags'))
                ->badge()
                ->separator(', ')
                ->color('info'),
        ];
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
