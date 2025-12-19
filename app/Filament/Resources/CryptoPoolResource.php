<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use App\Models\CryptoPool;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Services\Tools\Defillama;
use Filament\Actions\DeleteAction;
use App\Enums\CryptoPoolUpdateMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\CryptoPoolResource\Pages\EditCryptoPool;
use App\Filament\Resources\CryptoPoolResource\Pages\ListCryptoPools;
use App\Filament\Resources\CryptoPoolResource\Pages\CreateCryptoPool;

class CryptoPoolResource extends Resource
{
    protected static ?string $model = CryptoPool::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Crypto Pool Tracker';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Pool Information'))->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('url')
                        ->label(__('URL'))
                        ->url(),
                    Select::make('asset_id')
                        ->label(__('Asset'))
                        ->relationship('asset', 'name')
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make(__('Update Configuration'))->schema([
                    Select::make('update_method')
                        ->label(__('Update Method'))
                        ->required()
                        ->options(CryptoPoolUpdateMethod::class)
                        ->helperText(__('Method used to update pool data'))
                        ->live(),
                    TextInput::make('update_data.pool_id')
                        ->label(__('Defillama Pool ID'))
                        ->helperText(__('The Defillama pool ID (e.g., d9fa8e14-0447-4207-9ae8-7810199dfa1f)'))
                        ->required(fn(Get $get): bool => $get('update_method') === CryptoPoolUpdateMethod::DEFILLAMA)
                        ->visible(fn(Get $get): bool => $get('update_method') === CryptoPoolUpdateMethod::DEFILLAMA),
                ])->columns(1),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('liquidity')
                    ->label(__('Liquidity'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : '-'),
                TextColumn::make('apy')
                    ->label(__('APY'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . '%' : '-'),
                TextColumn::make('url')
                    ->label(__('URL'))
                    ->url(fn($record) => $record->url)
                    ->openUrlInNewTab(),
                TextColumn::make('update_method')
                    ->label(__('Update Method'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('asset.name')
                    ->label(__('Asset'))
                    ->sortable(),
                TextColumn::make('last_update')
                    ->label(__('Last Update'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('update')
                    ->label(__('Update'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (CryptoPool $record) {
                        try {
                            if (!$record->update_method) {
                                throw new \Exception(__('Update method is not configured'));
                            }

                            if ($record->update_method === CryptoPoolUpdateMethod::DEFILLAMA) {
                                $service = new Defillama($record);
                                $service->updateApy();

                                Notification::make()
                                    ->title(__('Pool updated successfully'))
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Pool update failed'))
                                ->body(__('Failed to update pool: :message', ['message' => $e->getMessage()]))
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
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
            'index' => ListCryptoPools::route('/'),
            'create' => CreateCryptoPool::route('/create'),
            'edit' => EditCryptoPool::route('/{record}/edit'),
        ];
    }
}
