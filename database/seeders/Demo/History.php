<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Asset;
use App\Models\Transfer;
use App\Models\Cotation;
use App\Models\CotationHistory;
use App\Enums\TransferType;
use App\Enums\CotationUpdateMethod;
use Illuminate\Database\Seeder;

class History extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $this->createHistory($user);
        $this->createTransfers($user);
    }

    public function createHistory(User $user)
    {
        // $cotations = Cotation::where('update_method', '!=', CotationUpdateMethod::FIXED)->where('update_method', '!=', CotationUpdateMethod::MANUAL)->get();
        $cotations = Cotation::all();
        $histories = collect([]);

        foreach ($cotations as $cotation) {
            $initialValue = $cotation->value;
            $initialValueMainCurrency = $cotation->value_main_currency;
            for ($i = 0; $i < 365; $i++) {
                $percent = rand(-2, 2)  / 100;
                $newValue = $initialValue + ($initialValue * $percent);
                $newValueMainCurrency = $initialValueMainCurrency + ($initialValueMainCurrency * $percent);
                if (($cotation->update_method === CotationUpdateMethod::FIXED) || ($cotation->update_method === CotationUpdateMethod::MANUAL)) {
                    $newValue = $cotation->value;
                    $newValueMainCurrency = $cotation->value_main_currency;
                }
                $histories->push([
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'value' => $newValue,
                    'value_main_currency' => $newValueMainCurrency,
                    'user_id' => $user->id,
                    'cotation_id' => $cotation->id,
                    'created_at' => now()->subDays($i),
                    'updated_at' => now()->subDays($i),
                ]);
                $initialValue = $newValue;
                $initialValueMainCurrency = $newValueMainCurrency;
            }
        }
        $chunks = $histories->chunk(100);
        foreach ($chunks as $chunk) {
            CotationHistory::insert($chunk->toArray());
        }
    }

    public function createTransfers(User $user)
    {
        $source = Asset::where('name', 'Boursorama Compte courant')->first();
        $destination = Asset::where('name', 'Microsoft')->first();

        Transfer::create([
            'type' => TransferType::Transfer,
            'source_id' => $source->id,
            'destination_id' => $destination->id,
            'source_quantity' => 1200,
            'destination_quantity' => 3,
            'date' => now()->subMonths(3),
            'user_id' => $user->id
        ]);
        Transfer::create([
            'type' => TransferType::Income,
            'destination_id' => $source->id,
            'destination_quantity' => 2200,
            'date' => now()->subMonths(4),
            'user_id' => $user->id
        ]);
    }
}
