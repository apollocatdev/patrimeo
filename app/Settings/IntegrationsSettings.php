<?php

namespace App\Settings;

use ApollocatDev\FilamentSettings\Contracts\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class IntegrationsSettings extends Settings
{
    public string $weboobPath = '';
    public string $finarySharingLink = '';
    public string $finarySecureCode = '';
    public string $coingeckoApiKey = '';

    public static function default(): static
    {
        $instance = new static();
        $instance->weboobPath = env('WOOB_BINARY_PATH', '/usr/bin/woob');
        $instance->finarySharingLink = '';
        $instance->finarySecureCode = '';
        $instance->coingeckoApiKey = '';
        return $instance;
    }

    public function getLabel(): string
    {
        return 'Integrations Settings';
    }

    public function getDescription(): ?string
    {
        return 'Configure external service integrations for data import and API access';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-puzzle-piece';
    }

    public function getSortOrder(): int
    {
        return 4;
    }

    public function getFormSchema(): array
    {
        return [
            Section::make('Weboob Integration')
                ->schema([
                    TextInput::make('weboobPath')
                        ->label('Weboob Binary Path')
                        ->helperText('Path to the Weboob binary executable')
                        ->required(),
                ]),
            Section::make('Finary Integration')
                ->schema([
                    TextInput::make('finarySharingLink')
                        ->label('Finary Sharing Link')
                        ->helperText('Your Finary sharing link for portfolio import'),
                    TextInput::make('finarySecureCode')
                        ->label('Finary Secure Code')
                        ->helperText('Your Finary secure code for authentication'),
                ]),
            Section::make('CoinGecko Integration')
                ->schema([
                    TextInput::make('coingeckoApiKey')
                        ->label('CoinGecko API Key')
                        ->helperText('API key for CoinGecko cryptocurrency data'),
                ]),
        ];
    }
}
