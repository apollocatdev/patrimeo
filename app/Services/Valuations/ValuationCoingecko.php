<?php

namespace App\Services\Valuations;

use App\Models\Valuation;
use App\Services\ValuationInterface;
use App\Exceptions\ValuationException;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Http;
use App\Settings\IntegrationsSettings;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class ValuationCoingecko implements ValuationInterface
{
    protected Valuation $valuation;

    public function __construct(Valuation $valuation)
    {
        $this->valuation = $valuation;
    }

    public function getQuote(): float
    {
        $coinName = $this->valuation->update_data['coin_name'] ?? 'bitcoin';
        $vsCurrency = strtolower($this->valuation->currency->symbol ?? 'usd');

        // Get API key from settings if available
        /** @var IntegrationsSettings $settings */
        $settings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $this->valuation->user_id);
        $apiKey = $settings->coingeckoApiKey;

        $headers = [];
        if ($apiKey) {
            $headers['x-cg-demo-api-key'] = $apiKey;
        }

        $response = Http::withHeaders($headers)->get('https://api.coingecko.com/api/v3/coins/' . $coinName . '/market_chart', [
            'vs_currency' => $vsCurrency,
            'days' => 1,
            'interval' => 'daily',
            'precision' => 4
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['prices']) && !empty($data['prices'])) {
                // Get the latest price (last element in the prices array)
                $latestPrice = end($data['prices']);
                return (float) $latestPrice[1];
            }
        }

        throw new ValuationException($this->valuation, __('Failed to fetch price from Coingecko API'), $response->status(), $response->body());
    }

    public static function getFields(): array
    {
        return [
            'coin_name' => TextInput::make('coin_name')
                ->label(__('Coin name (e.g., bitcoin, ethereum)'))
        ];
    }
}
