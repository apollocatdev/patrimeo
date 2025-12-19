<?php

namespace App\Settings;

use App\Enums\ValuationUpdateMethod;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use ApollocatDev\FilamentSettings\Contracts\Settings;

class ValuationUpdateSettings extends Settings
{
    public int $minHoursBetweenUpdates = 0;
    public array $rateLimiters = [];
    public int $defaultRateLimitSeconds = 1;

    public static function default(): static
    {
        $instance = new static();
        $instance->minHoursBetweenUpdates = 0;
        $instance->rateLimiters = [
            [
                'service' => 'yahoo',
                'rate_limit_seconds' => 3,
            ],
        ];
        $instance->defaultRateLimitSeconds = 1;
        return $instance;
    }

    public function getLabel(): string
    {
        return 'Valuation Update Settings';
    }

    public function getDescription(): ?string
    {
        return 'Configure valuation update frequency and rate limiting for different services';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-arrow-path';
    }

    public function getSortOrder(): int
    {
        return 2;
    }

    public function getFormSchema(): array
    {
        return [
            Group::make()->schema([
                Section::make('Update Frequency')
                    ->description('Configure how often valuations can be updated')
                    ->schema([
                        TextInput::make('minHoursBetweenUpdates')
                            ->label('Minimum Hours Between Updates')
                            ->helperText('Minimum number of hours to wait before updating the same valuation again. Set to 0 to disable this restriction.')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(168) // 1 week max
                            ->required(),
                    ]),
                Section::make('Rate Limiters')
                    ->description('Configure rate limiting for different valuation services to avoid being blocked')
                    ->schema([
                        TextInput::make('defaultRateLimitSeconds')
                            ->label('Default Rate Limit (seconds)')
                            ->helperText('Fallback rate limit for services not specifically configured below')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(3600) // 1 hour max
                            ->required(),
                        Repeater::make('rateLimiters')
                            ->label('Service Rate Limits')
                            ->schema([
                                Select::make('service')
                                    ->label('Service')
                                    ->options(ValuationUpdateMethod::dropdown())
                                    ->required()
                                    ->searchable(),
                                TextInput::make('rate_limit_seconds')
                                    ->label('Rate Limit (seconds)')
                                    ->helperText('Minimum seconds to wait between requests to this service')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(3600) // 1 hour max
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Rate Limiter')
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['service'] ? ValuationUpdateMethod::from($state['service'])->getLabel() . ' (' . $state['rate_limit_seconds'] . 's)' : null
                            ),
                    ])
            ])
        ];
    }

    /**
     * Get rate limiters as an associative array for backward compatibility
     */
    public function getRateLimitersArray(): array
    {
        $rateLimiters = [];
        foreach ($this->rateLimiters as $rateLimiter) {
            $rateLimiters[$rateLimiter['service']] = $rateLimiter['rate_limit_seconds'];
        }
        return $rateLimiters;
    }

    /**
     * Get rate limit for a specific service
     */
    public function getRateLimitForService(string $service): int
    {
        foreach ($this->rateLimiters as $rateLimiter) {
            if ($rateLimiter['service'] === $service) {
                return $rateLimiter['rate_limit_seconds'];
            }
        }

        // Return configurable default rate limit if service not found
        return $this->defaultRateLimitSeconds;
    }
}
