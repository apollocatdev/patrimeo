<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Data\ImportRecord;
use App\Models\User;
use App\Models\Cotation;
use App\Models\Envelop;
use App\Models\AssetClass;
use App\Models\Currency;
use App\Services\ImportMapper;
use App\Services\Importers\ImporterExample;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportMapperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Auth::login($user);
    }

    public function test_map_record_envelop_and_asset_class_and_cotation(): void
    {
        $envelop = Envelop::create(['name' => 'Investment Account', 'user_id' => Auth::id()]);
        $class = AssetClass::create(['name' => 'Stocks', 'user_id' => Auth::id()]);
        $currency = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => Auth::id()]);
        $cot = Cotation::create(['name' => 'AAPL', 'currency_id' => $currency->id, 'user_id' => Auth::id()]);

        $mapper = new ImportMapper();
        $record = ImportRecord::fromArray([
            'name' => 'Example Stock',
            'account_name' => 'Broker Account',
            'class' => 'Stocks',
            'envelop' => 'Investment Account',
            'quantity' => 12.0,
            'currency' => 'EUR',
            'isin' => null,
            'symbol' => 'AAPL',
        ]);

        $mapped = $mapper->mapRecords([$record]);
        $this->assertCount(1, $mapped);
        $mappings = $mapped[0]['mappings'];
        $this->assertSame($envelop->id, $mappings['envelop']['existing_id']);
        $this->assertSame($class->id, $mappings['asset_class']['existing_id']);
    }

    public function test_map_record_cash_asset_uses_currency_for_cotation(): void
    {
        Currency::create(['symbol' => 'USD', 'main' => false, 'user_id' => Auth::id()]);
        $eur = Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => Auth::id()]);
        // Cotation named EUR should be suggested
        $cot = Cotation::create(['name' => 'EUR', 'currency_id' => $eur->id, 'user_id' => Auth::id()]);

        $mapper = new ImportMapper(new ImporterExample([]));
        $record = ImportRecord::fromArray([
            'name' => 'Cash Account',
            'class' => 'Cash',
            'envelop' => 'Bank',
            'quantity' => 100.0,
            'currency' => 'EUR',
        ]);

        $mapped = $mapper->mapRecords([$record]);
        $this->assertSame($cot->id, $mapped[0]['mappings']['cotation']['existing_id']);
    }
}

