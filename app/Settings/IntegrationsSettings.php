<?php

namespace App\Settings;

use ApollocatDev\FilamentSettings\Contracts\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class IntegrationsSettings extends Settings
{
    public string $weboobPath = '';
    public ?string $finarySharingLink = null;
    public ?string $finarySecureCode = null;
    public ?string $ankrApiKey = null;
    public ?string $coingeckoApiKey = null;
    public ?string $telegramBotToken = null;
    public ?string $telegramChatId = null;
    public ?string $lunchflowApiToken = null;

    public static function default(): static
    {
        $instance = new static();
        $instance->weboobPath = env('WOOB_BINARY_PATH', '/usr/bin/woob');
        $instance->finarySharingLink = null;
        $instance->finarySecureCode = null;
        $instance->ankrApiKey = null;
        $instance->coingeckoApiKey = null;
        $instance->telegramBotToken = null;
        $instance->telegramChatId = null;
        $instance->lunchflowApiToken = null;
        return $instance;
    }

    public function getLabel(): string
    {
        return __('Integrations Settings');
    }

    public function getDescription(): ?string
    {
        return __('Configure external service integrations for data import and API access');
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
            Section::make(__('Weboob Integration'))
                ->schema([
                    TextInput::make('weboobPath')
                        ->label(__('Weboob Binary Path'))
                        ->helperText('Path to the Weboob binary executable')
                        ->required(),
                ]),
            Section::make(__('Finary Integration'))
                ->schema([
                    TextInput::make('finarySharingLink')
                        ->label(__('Finary Sharing Link'))
                        ->helperText('Your Finary sharing link for portfolio import'),
                    TextInput::make('finarySecureCode')
                        ->label(__('Finary Secure Code'))
                        ->helperText('Your Finary secure code for authentication'),
                ]),
            Section::make(__('Ankr Integration'))
                ->schema([
                    TextInput::make('ankrApiKey')
                        ->label(__('Ankr API Key'))
                        ->helperText('API key for Ankr blockchain data'),
                ]),
            Section::make(__('CoinGecko Integration'))
                ->schema([
                    TextInput::make('coingeckoApiKey')
                        ->label(__('CoinGecko API Key'))
                        ->helperText('API key for CoinGecko cryptocurrency data'),
                ]),
            Section::make(__('Telegram integration'))
                ->schema([
                    TextInput::make('telegramBotToken')
                        ->label(__('Telegram Bot Token'))
                        ->helperText('Token for Telegram bot'),
                    TextInput::make('telegramChatId')
                        ->label(__('Telegram Chat ID'))
                        ->helperText('Chat ID for Telegram bot. You can find it by sending a message to the bot and checking the chat ID in the response.'),
                ]),
            Section::make(__('Lunch Flow Integration'))
                ->schema([
                    TextInput::make('lunchflowApiToken')
                        ->label(__('Lunch Flow API Token'))
                        ->helperText('API token for Lunch Flow service'),
                ]),
        ];
    }
}
