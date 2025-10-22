<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable observers for testing
        Currency::unsetEventDispatcher();
    }

    public function test_get_default_returns_main_currency(): void
    {
        $user = User::factory()->create();

        // Create non-main currency
        Currency::create(['symbol' => 'USD', 'main' => false, 'user_id' => $user->id]);

        // Create main currency
        $mainCurrency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        $defaultCurrency = Currency::getDefault();

        $this->assertNotNull($defaultCurrency);
        $this->assertEquals($mainCurrency->id, $defaultCurrency->id);
        $this->assertEquals('EUR', $defaultCurrency->symbol);
        $this->assertTrue($defaultCurrency->main);
    }

    public function test_get_default_returns_null_when_no_main_currency(): void
    {
        $user = User::factory()->create();

        // Create only non-main currencies
        Currency::create(['symbol' => 'USD', 'main' => false, 'user_id' => $user->id]);
        Currency::create(['symbol' => 'GBP', 'main' => false, 'user_id' => $user->id]);

        $defaultCurrency = Currency::getDefault();

        $this->assertNull($defaultCurrency);
    }

    public function test_assets_relationship_exists(): void
    {
        $user = User::factory()->create();
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id]);

        // Just verify the relationship method exists
        $this->assertTrue(method_exists($currency, 'assets'));
    }
}
