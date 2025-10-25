<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use App\Models\Asset;
use App\Models\Valuation;
use App\Models\Currency;
use App\Models\Envelop;
use App\Observers\AssetObserver;
use App\Observers\ValuationObserver;
use App\Observers\CurrencyObserver;
use App\Observers\EnvelopObserver;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;
use App\Settings\LocalizationSettings;
use App\Settings\IntegrationsSettings;
use App\Settings\EmailSettings;
use App\Settings\VariousSettings;
use App\Settings\ValuationUpdateSettings;
use App\Settings\TransactionsSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        FilamentAsset::register([
            Css::make('custom-stylesheet', __DIR__ . '/../../resources/css/custom.css'),
        ]);
        // FilamentAsset::register([
        //     Js::make('chart-js-plugins', Vite::asset('resources/js/filament-chart-js-plugins.js'))->module(),
        // ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Collection::macro('dataGet', function ($key, $default = null) {
            /** @var Collection $this */
            return collect(data_get($this->items, $key, $default));
        });

        // Register model observers for automatic integrity checks
        Asset::observe(AssetObserver::class);
        Valuation::observe(ValuationObserver::class);
        Currency::observe(CurrencyObserver::class);
        Envelop::observe(EnvelopObserver::class);

        // Register Filament Settings
        FilamentSettings::addSettings(LocalizationSettings::class);
        FilamentSettings::addSettings(IntegrationsSettings::class);
        FilamentSettings::addSettings(EmailSettings::class);
        FilamentSettings::addSettings(VariousSettings::class);
        FilamentSettings::addSettings(ValuationUpdateSettings::class);
        FilamentSettings::addSettings(TransactionsSettings::class);
    }
}
