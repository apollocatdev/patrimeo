<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Currency;
use App\Models\Valuation;
use App\Enums\ValuationUpdateMethod;
use App\Services\Valuations\ValuationYahoo;
use App\Services\Valuations\ValuationCss;
use App\Services\Valuations\ValuationCoingecko;
use App\Services\Valuations\ValuationCommand;
use App\Services\Valuations\ValuationOpenAI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValuationServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Auth::login($user);
        Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => Auth::id()]);
    }

    public function test_yahoo_success(): void
    {
        $currency = Currency::first();
        $c = Valuation::create([
            'name' => 'AAPL',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::YAHOO,
            'update_data' => ['symbol' => 'AAPL'],
            'user_id' => Auth::id(),
        ]);

        Http::fake([
            'query1.finance.yahoo.com/*' => Http::response([
                'chart' => [
                    'result' => [[
                        'meta' => ['regularMarketPrice' => 189.12],
                    ]],
                ],
            ], 200),
        ]);

        $service = new ValuationYahoo($c);
        $this->assertSame(189.12, $service->getQuote());
    }

    public function test_css_selector_success(): void
    {
        $currency = Currency::first();
        $c = Valuation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::CSS,
            'update_data' => ['url' => 'https://example.test/page', 'selector' => '#price'],
            'user_id' => Auth::id(),
        ]);

        Http::fake([
            'example.test/*' => Http::response('<div id="price">1 234,56 €</div>', 200),
        ]);

        $service = new ValuationCss($c);
        $this->assertSame(1234.56, $service->getQuote());
    }

    public function test_coingecko_success(): void
    {
        $currency = Currency::first();
        $c = Valuation::create([
            'name' => 'BTC',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::COINGECKO,
            'update_data' => ['coin_name' => 'bitcoin'],
            'user_id' => Auth::id(),
        ]);

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'prices' => [
                    [1734489600000, 65000.12],
                    [1734576000000, 65234.56],
                ],
            ], 200),
        ]);

        $service = new ValuationCoingecko($c);
        $this->assertSame(65234.56, $service->getQuote());
    }

    public function test_coingecko_with_api_key(): void
    {
        $currency = Currency::first();
        $c = Valuation::create([
            'name' => 'ETH',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::COINGECKO,
            'update_data' => ['coin_name' => 'ethereum'],
            'user_id' => Auth::id(),
        ]);

        // Create a setting with API key
        $settings = \App\Settings\IntegrationsSettings::get();
        $settings->coingeckoApiKey = 'test-api-key-123';
        $settings->save();

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'prices' => [
                    [1734489600000, 2500.00],
                    [1734576000000, 2550.00],
                ],
            ], 200),
        ]);

        $service = new ValuationCoingecko($c);
        $this->assertSame(2550.00, $service->getQuote());
    }

    public function test_command_cotation_success(): void
    {
        $currency = Currency::first();
        $c = Valuation::create([
            'name' => 'CMD',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::COMMAND,
            'update_data' => ['command' => 'php -r "echo 123.45;"'],
            'user_id' => Auth::id(),
        ]);

        $service = new ValuationCommand($c);
        $this->assertSame(123.45, $service->getQuote());
    }

    public function test_openai_success_with_currency_match(): void
    {
        config()->set('services.openai.api_key', 'test-key');

        $currency = Currency::first();
        // Ensure currency code exists as property for service check
        // Here, symbol is used as code in model
        $c = Valuation::create([
            'name' => 'ACME',
            'currency_id' => $currency->id,
            'update_method' => ValuationUpdateMethod::OPENAI,
            'user_id' => Auth::id(),
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'output_text' => json_encode([
                    'price' => '10.50',
                    'currency' => 'EUR',
                    'date' => '2025-01-01',
                ]),
            ], 200),
        ]);

        $service = new ValuationOpenAI($c);
        $this->assertSame(10.50, $service->getQuote());
    }
}
