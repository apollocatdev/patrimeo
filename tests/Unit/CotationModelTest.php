<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cotation;
use App\Models\Currency;
use App\Models\CotationUpdate;
use App\Models\CotationHistory;
use App\Enums\CotationUpdateMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CotationModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable observers for testing
        Cotation::unsetEventDispatcher();
    }

    public function test_get_rate_limiter_key_for_yahoo(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'AAPL',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::YAHOO,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('yahoo', $cotation->rate_limiter_key);
    }

    public function test_get_rate_limiter_key_for_xpath(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::XPATH,
            'update_data' => ['url' => 'https://example.com/page'],
            'user_id' => $user->id,
        ]);

        $this->assertEquals('example.com', $cotation->rate_limiter_key);
    }

    public function test_get_rate_limiter_key_for_openai(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::OPENAI,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('openai', $cotation->rate_limiter_key);
    }

    public function test_get_rate_limiter_key_for_unknown_method(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::FIXED,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('none', $cotation->rate_limiter_key);
    }

    public function test_last_update_relationship(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::FIXED,
            'user_id' => $user->id,
        ]);

        // Create older update
        CotationUpdate::create([
            'cotation_id' => $cotation->id,
            'date' => now()->subDays(2),
            'value' => 100.0,
            'status' => 'success',
            'user_id' => $user->id,
        ]);

        // Create newer update
        $newerUpdate = CotationUpdate::create([
            'cotation_id' => $cotation->id,
            'date' => now(),
            'value' => 110.0,
            'status' => 'success',
            'user_id' => $user->id,
        ]);

        $this->assertEquals($newerUpdate->id, $cotation->lastUpdate()->id);
    }

    public function test_get_update_periodicity_attribute(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::YAHOO,
            'user_id' => $user->id,
        ]);

        $periodicity = $cotation->update_periodicity;

        $this->assertIsArray($periodicity);
        // With new Settings framework, should return default values
        $this->assertSame(['value' => 6, 'type' => 'hour'], $periodicity);
    }

    public function test_get_update_periodicity_text_attribute(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $currency->id,
            'update_method' => CotationUpdateMethod::YAHOO,
            'user_id' => $user->id,
        ]);

        $periodicityText = $cotation->update_periodicity_text;

        $this->assertIsString($periodicityText);
        // With new Settings framework, should return default periodicity text
        $this->assertEquals('every 6 hour(s)', $periodicityText);
    }
}
