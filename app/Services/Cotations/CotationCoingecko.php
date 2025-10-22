<?php

namespace App\Services\Cotations;

use App\Models\Cotation;
use App\Services\CotationInterface;
use App\Exceptions\CotationException;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Http;
use App\Settings\IntegrationsSettings;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class CotationCoingecko implements CotationInterface
{
    protected Cotation $cotation;

    public function __construct(Cotation $cotation)
    {
        $this->cotation = $cotation;
    }

    public function getQuote(): float
    {
        $coinName = $this->cotation->update_data['coin_name'] ?? 'bitcoin';
        $vsCurrency = strtolower($this->cotation->currency->symbol ?? 'usd');

        // Get API key from settings if available
        /** @var IntegrationsSettings $settings */
        $settings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $this->cotation->user_id);
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

        throw new CotationException($this->cotation, __('Failed to fetch price from Coingecko API'), $response->status(), $response->body());
    }

    public static function getFields(): array
    {
        return [
            'coin_name' => TextInput::make('coin_name')
                ->label(__('Coin name (e.g., bitcoin, ethereum)'))
        ];
    }
}
