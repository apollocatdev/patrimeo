<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Currency as CurrencyModel;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Currency as CurrencyHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrencyHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Auth::login($user);

        CurrencyModel::create(['symbol' => 'EUR', 'main' => true, 'user_id' => Auth::id()]);
        $settings = \App\Settings\LocalizationSettings::get();
        $settings->numberFormat = 'fr';
        $settings->dateFormat = 'fr';
        $settings->dateFormatSeparator = '/';
        $settings->save();
    }

    public function test_sanitize_to_float(): void
    {
        $this->assertSame(1234.56, CurrencyHelper::sanitizeToFloat('1 234,56€'));
        $this->assertSame(12.0, CurrencyHelper::sanitizeToFloat('12'));
        $this->assertSame(0.0, CurrencyHelper::sanitizeToFloat('abc'));
    }

    public function test_to_currency_formats_string(): void
    {
        $formatted = CurrencyHelper::toCurrency(1234.56);
        $this->assertIsString($formatted);
        $this->assertNotSame('', $formatted);
    }
}
