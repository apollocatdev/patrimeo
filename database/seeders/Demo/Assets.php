<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Asset;
use App\Models\Envelop;
use App\Models\Valuation;
use App\Models\AssetClass;
use Illuminate\Database\Seeder;
use App\Enums\ValuationUpdateMethod;
use App\Enums\TransactionUpdateMethod;

class Assets extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $envelops = Envelop::pluck('id', 'name');
        $cotations = Cotation::pluck('id', 'name');
        $assetClasses = AssetClass::pluck('id', 'name');

        $assets = [
            ['name' => 'SocGen compte courant', 'envelop' => 'SocGen',  'class' => 'Cash', 'cotation' => 'EUR', 'quantity' => 7300],
            ['name' => 'Boursorama Compte courant', 'envelop' => 'Boursorama Banque',  'class' => 'Cash', 'cotation' => 'EUR', 'quantity' => 1100],
            ['name' => 'Bitcoin', 'envelop' => 'Ledger 1',  'class' => 'Crypto', 'cotation' => 'BTC', 'quantity' => 0.45],
            ['name' => 'USDC', 'envelop' => 'AAVE',  'class' => 'Crypto', 'cotation' => 'USDC', 'quantity' => 12150],
            ['name' => 'Amundi MSCI World UCITS ETF', 'envelop' => 'PEA Fortuneo',  'class' => 'Actions', 'cotation' => 'Amundi CW8 MSCI World', 'quantity' => 314],
            ['name' => 'Amundi PEA MSCI Emerging Asia UCITS ETF', 'envelop' => 'PEA Fortuneo',  'class' => 'Actions', 'cotation' => 'Amundi MSCI EM ASIA', 'quantity' => 640],
            ['name' => 'Cash PEA', 'envelop' => 'PEA Fortuneo',  'class' => 'Cash', 'cotation' => 'EUR', 'quantity' => 1981.25],
            ['name' => 'Alphabet', 'envelop' => 'Trade Republic',  'class' => 'Actions', 'cotation' => 'Alphabet', 'quantity' => 30],
            ['name' => 'Microsoft', 'envelop' => 'Trade Republic',  'class' => 'Actions', 'cotation' => 'Microsoft', 'quantity' => 6],
            ['name' => 'Cash CTO', 'envelop' => 'Trade Republic',  'class' => 'Cash', 'cotation' => 'EUR', 'quantity' => 7201.88],
            ['name' => 'Linxea SURAVENIR', 'envelop' => 'Linxeo Suravenir',  'class' => 'Obligations', 'cotation' => 'Fonds euros Suravenir Rendement', 'quantity' => 1],
            ['name' => 'EFImmo 1', 'envelop' => 'Linxeo Suravenir',  'class' => 'Real-estate', 'cotation' => 'Efimmo 1', 'quantity' => 211.6842],
            ['name' => 'PFO2', 'envelop' => 'Linxeo Suravenir',  'class' => 'Real-estate', 'cotation' => 'PFO2', 'quantity' => 112.6026],
            ['name' => 'Gold - Napoleon 20 Frcs', 'envelop' => 'Domicile',  'class' => 'Precious metal', 'cotation' => 'Gold - 20 Francs Napoléon', 'quantity' => 72],
        ];

        foreach ($assets as $asset) {
            $asset['user_id'] = $user->id;
            $asset['envelop_id'] = $envelops[$asset['envelop']];
            $asset['cotation_id'] = $cotations[$asset['cotation']];
            $asset['class_id'] = $assetClasses[$asset['class']];
            $asset['update_method'] = TransactionUpdateMethod::MANUAL;

            unset($asset['envelop']);
            unset($asset['cotation']);
            unset($asset['class']);
            Asset::withoutEvents(function () use ($asset) {
                Asset::create($asset);
            });
        }
    }
}
