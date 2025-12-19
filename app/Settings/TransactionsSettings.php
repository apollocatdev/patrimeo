<?php

namespace App\Settings;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use ApollocatDev\FilamentSettings\Contracts\Settings;

class TransactionsSettings extends Settings
{
    public int $minHoursBetweenUpdates = 0;

    public static function default(): static
    {
        $instance = new static();
        $instance->minHoursBetweenUpdates = 0;
        return $instance;
    }

    public function getLabel(): string
    {
        return 'Transaction Settings';
    }

    public function getDescription(): ?string
    {
        return 'Configure transaction update frequency and restrictions';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-arrow-path';
    }

    public function getSortOrder(): int
    {
        return 3;
    }

    public function getFormSchema(): array
    {
        return [
            Group::make()->schema([
                Section::make('Update Frequency')
                    ->description('Configure how often transactions can be updated')
                    ->schema([
                        TextInput::make('minHoursBetweenUpdates')
                            ->label('Minimum Hours Between Updates')
                            ->helperText('Minimum number of hours to wait before updating the same asset transactions again. Set to 0 to disable this restriction.')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ]),
            ])
        ];
    }
}
