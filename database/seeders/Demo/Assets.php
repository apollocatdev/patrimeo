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
        $valuations = Valuation::pluck('id', 'name');
        $assetClasses = AssetClass::pluck('id', 'name');

        $assets = [
            ['name' => 'SocGen compte courant', 'envelop' => 'SocGen',  'class' => 'Cash', 'valuation' => 'EUR', 'quantity' => 7300],
            ['name' => 'Boursorama Compte courant', 'envelop' => 'Boursorama Banque',  'class' => 'Cash', 'valuation' => 'EUR', 'quantity' => 1100],
            ['name' => 'Bitcoin', 'envelop' => 'Ledger 1',  'class' => 'Crypto', 'valuation' => 'BTC', 'quantity' => 0.45],
            ['name' => 'USDC', 'envelop' => 'AAVE',  'class' => 'Crypto', 'valuation' => 'USDC', 'quantity' => 12150],
            ['name' => 'Amundi MSCI World UCITS ETF', 'envelop' => 'PEA Fortuneo',  'class' => 'Actions', 'valuation' => 'Amundi CW8 MSCI World', 'quantity' => 314],
            ['name' => 'Amundi PEA MSCI Emerging Asia UCITS ETF', 'envelop' => 'PEA Fortuneo',  'class' => 'Actions', 'valuation' => 'Amundi MSCI EM ASIA', 'quantity' => 640],
            ['name' => 'Cash PEA', 'envelop' => 'PEA Fortuneo',  'class' => 'Cash', 'valuation' => 'EUR', 'quantity' => 1981.25],
            ['name' => 'Alphabet', 'envelop' => 'Trade Republic',  'class' => 'Actions', 'valuation' => 'Alphabet', 'quantity' => 30],
            ['name' => 'Microsoft', 'envelop' => 'Trade Republic',  'class' => 'Actions', 'valuation' => 'Microsoft', 'quantity' => 6],
            ['name' => 'Cash CTO', 'envelop' => 'Trade Republic',  'class' => 'Cash', 'valuation' => 'EUR', 'quantity' => 7201.88],
            ['name' => 'Linxea SURAVENIR', 'envelop' => 'Linxeo Suravenir',  'class' => 'Obligations', 'valuation' => 'Fonds euros Suravenir Rendement', 'quantity' => 1],
            ['name' => 'EFImmo 1', 'envelop' => 'Linxeo Suravenir',  'class' => 'Real-estate', 'valuation' => 'Efimmo 1', 'quantity' => 211.6842],
            ['name' => 'PFO2', 'envelop' => 'Linxeo Suravenir',  'class' => 'Real-estate', 'valuation' => 'PFO2', 'quantity' => 112.6026],
            ['name' => 'Gold - Napoleon 20 Frcs', 'envelop' => 'Domicile',  'class' => 'Precious metal', 'valuation' => 'Gold - 20 Francs Napoléon', 'quantity' => 72],
        ];

        foreach ($assets as $asset) {
            $asset['user_id'] = $user->id;
            $asset['envelop_id'] = $envelops[$asset['envelop']];
            $asset['valuation_id'] = $valuations[$asset['valuation']];
            $asset['class_id'] = $assetClasses[$asset['class']];
            $asset['update_method'] = TransactionUpdateMethod::MANUAL;

            unset($asset['envelop']);
            unset($asset['valuation']);
            unset($asset['class']);
            Asset::withoutEvents(function () use ($asset) {
                Asset::create($asset);
            });
        }
    }
}
