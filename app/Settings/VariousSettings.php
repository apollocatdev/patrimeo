<?php

namespace App\Settings;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use ApollocatDev\FilamentSettings\Contracts\Settings;

class VariousSettings extends Settings
{
    public bool $dashboardCaching = true;
    public int $dashboardCachingTime = 30;
    public int $sessionLifetime = 120;
    public bool $expireSessionOnBrowserClose = false;
    public string $chartsTheme = 'palette6';
    public string $valuationLogLevel = 'debug';
    public string $transactionsLogLevel = 'debug';
    public string $dashboardsLogLevel = 'debug';
    public string $schedulerLogLevel = 'debug';
    public string $toolsLogLevel = 'debug';

    public static function default(): static
    {
        $instance = new static();
        $instance->dashboardCaching = true;
        $instance->dashboardCachingTime = 30;
        $instance->sessionLifetime = 120;
        $instance->expireSessionOnBrowserClose = false;
        $instance->chartsTheme = 'palette6';
        $instance->valuationLogLevel = 'debug';
        $instance->transactionsLogLevel = 'debug';
        $instance->dashboardsLogLevel = 'debug';
        $instance->schedulerLogLevel = 'debug';
        $instance->toolsLogLevel = 'debug';
        return $instance;
    }

    public function getLabel(): string
    {
        return 'General Settings';
    }

    public function getDescription(): ?string
    {
        return 'Configure dashboard caching, security, charts, and logging preferences';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public function getSortOrder(): int
    {
        return 1;
    }

    public function getFormSchema(): array
    {
        return [
            Group::make()->schema([
                Section::make('Dashboard Caching')
                    ->description('Configure dashboard caching settings')
                    ->schema([
                        Toggle::make('dashboardCaching')
                            ->label('Enable Dashboard Caching')
                            ->helperText('Cache dashboard data to improve performance'),
                        TextInput::make('dashboardCachingTime')
                            ->label('Cache Expiration (minutes)')
                            ->type('number')
                            ->minValue(1)
                            ->helperText('How long to keep cached data')
                            ->visible(fn($get) => $get('dashboardCaching')),
                    ]),
                Section::make('Security')
                    ->description('Configure security and session settings')
                    ->schema([
                        TextInput::make('sessionLifetime')
                            ->label('Session Lifetime (minutes)')
                            ->helperText('Number of minutes a user can remain inactive before being logged out. Default: 120 minutes (2 hours)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1440)
                            ->required(),
                        Toggle::make('expireSessionOnBrowserClose')
                            ->label('Expire session when browser closes')
                            ->helperText('If enabled, users will be logged out when they close their browser. Default: disabled')
                            ->default(false),
                    ]),
                Section::make('Charts')
                    ->description('Configure chart appearance')
                    ->schema([
                        Select::make('chartsTheme')
                            ->label('Charts Theme')
                            ->options([
                                'palette1' => 'Palette 1',
                                'palette2' => 'Palette 2',
                                'palette3' => 'Palette 3',
                                'palette4' => 'Palette 4',
                                'palette5' => 'Palette 5',
                                'palette6' => 'Palette 6',
                            ])
                            ->required(),
                    ]),
                Section::make('Logging')
                    ->description('Configure logging levels for different components')
                    ->schema([
                        Select::make('valuationLogLevel')
                            ->label('Valuation Log Level')
                            ->options([
                                'none' => 'None',
                                'info' => 'Info',
                                'debug' => 'Debug',
                            ])
                            ->required(),
                        Select::make('transactionsLogLevel')
                            ->label('Transactions Log Level')
                            ->options([
                                'none' => 'None',
                                'info' => 'Info',
                                'debug' => 'Debug',
                            ])
                            ->required(),
                        Select::make('dashboardsLogLevel')
                            ->label('Dashboards Log Level')
                            ->options([
                                'none' => 'None',
                                'info' => 'Info',
                                'debug' => 'Debug',
                            ])
                            ->required(),
                        Select::make('schedulerLogLevel')
                            ->label('Scheduler Log Level')
                            ->options([
                                'none' => 'None',
                                'info' => 'Info',
                                'debug' => 'Debug',
                            ])
                            ->required(),
                        Select::make('toolsLogLevel')
                            ->label('Tools Log Level')
                            ->options([
                                'none' => 'None',
                                'info' => 'Info',
                                'debug' => 'Debug',
                            ])
                            ->required(),
                    ])
                    ->columns(3)
            ])->columns(2)
        ];
    }

    public function getDashboardCacheSettings(): array
    {
        return [
            'enabled' => $this->dashboardCaching,
            'expiration_minutes' => $this->dashboardCachingTime,
        ];
    }

    public function getSessionLifetime(): int
    {
        return $this->sessionLifetime;
    }

    public function getSessionExpireOnClose(): bool
    {
        return $this->expireSessionOnBrowserClose;
    }
}
