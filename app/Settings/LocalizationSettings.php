<?php

namespace App\Settings;

use ApollocatDev\FilamentSettings\Contracts\Settings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class LocalizationSettings extends Settings
{
    public string $numberFormat = 'fr';
    public string $dateFormat = 'fr';
    public string $dateFormatSeparator = '/';

    public static function default(): static
    {
        $instance = new static();
        $instance->numberFormat = 'fr';
        $instance->dateFormat = 'fr';
        $instance->dateFormatSeparator = '/';
        return $instance;
    }

    public function getLabel(): string
    {
        return 'Localization Settings';
    }

    public function getDescription(): ?string
    {
        return 'Configure number and date formatting preferences for your locale';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-globe-alt';
    }

    public function getSortOrder(): int
    {
        return 2;
    }

    public function getFormSchema(): array
    {
        return [
            Section::make('Localization')
                ->schema([
                    Select::make('numberFormat')
                        ->label('Number format')
                        ->options([
                            'fr' => 'French',
                            'en' => 'English',
                        ])
                        ->required(),
                    Select::make('dateFormat')
                        ->label('Date format')
                        ->options([
                            'fr' => 'French',
                            'en' => 'English',
                        ])
                        ->required(),
                    TextInput::make('dateFormatSeparator')
                        ->label('Date format separator')
                        ->required(),
                ])
                ->columns(1)
        ];
    }
}
