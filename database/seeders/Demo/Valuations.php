<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Valuation;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use App\Enums\ValuationUpdateMethod;

class Valuations extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $this->createValuations($user);
    }

    public function createValuations(User $user)
    {
        $currencies = Currency::pluck('id', 'symbol');

        $valuations = [
            ['name' => 'EUR', 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::FIXED, 'update_data' => null, 'value' => 1],
            ['name' => 'USDEUR', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'EUR=X'], 'value' => 0.88],
            ['name' => 'EURUSD', 'isin' => null, 'currency' => 'USD', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'EURUSD=X'], 'value' => 1.12],
            // ['name' => 'SocGen', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::MANUAL, 'value' => 7300],
            // ['name' => 'Boursorama Compte courant', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::MANUAL, 'value' => 1100 ],
            ['name' => 'BTC', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'BTC-EUR'], 'value' => 98423],
            ['name' => 'USDC', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'USDC-EUR'], 'value' => 0.89],
            // ['name' => 'Cash PEA', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::MANUAL, 'value' => 1981.25],
            // ['name' => 'Cash CTO', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::MANUAL, 'value' => 7201.88],
            ['name' => 'Amundi CW8 MSCI World', 'isin' => 'LU1681043599', 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'CW8.PA'], 'value' => 540.24],
            ['name' => 'Amundi MSCI EM ASIA', 'isin' => 'FR0013412012', 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'PAASI.PA'], 'value' => 24.997],
            ['name' => 'Alphabet', 'isin' => 'US38259P1089', 'currency' => 'USD', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'GOOGL'], 'value' => 168.2],
            ['name' => 'Microsoft', 'isin' => 'US5949181045', 'currency' => 'USD', 'update_method' => ValuationUpdateMethod::YAHOO, 'update_data' => ['symbol' => 'MSFT'], 'value' => 450.12],
            ['name' => 'Fonds euros Suravenir Rendement', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::MANUAL, 'update_data' => null, 'value' => 23871],
            ['name' => 'Efimmo 1', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::XPATH, 'update_data' => ['url' => "https://quantalys.com/SCPI/546", 'xpath' => "(//span[contains(@class, 'box-value')])[2]"], 'value' => 205.23],
            ['name' => 'PFO2', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::XPATH, 'update_data' => ['url' => "https://quantalys.com/SCPI/599", 'xpath' => "(//span[contains(@class, 'box-value')])[2]"], 'value' => 164.23],
            ['name' => 'Gold - 20 Francs Napoléon', 'isin' => null, 'currency' => 'EUR', 'update_method' => ValuationUpdateMethod::XPATH, 'update_data' => ['url' => 'https://www.ariva.de/20-ffrs-napoleon-mit-kranz-gold-kurs/kurse/historische-kurse?currency=EUR', 'xpath' => '(//table)[2]//tr[2]/td[5]'], 'value' => 542.12],
        ];

        foreach ($valuations as $valuation) {
            $valuation['currency_id'] = $currencies[$valuation['currency']];
            unset($valuation['currency']);
            $valuation['user_id'] = $user->id;
            $valuation['last_update'] = now();
            Valuation::create($valuation);
        }
    }
}
