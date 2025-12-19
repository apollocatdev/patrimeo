<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\Valuation;
use App\Models\ValuationHistory;
use App\Enums\TransactionType;
use App\Enums\ValuationUpdateMethod;
use Illuminate\Database\Seeder;

class History extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $this->createHistory($user);
        $this->createTransactions($user);
    }

    public function createHistory(User $user)
    {
        // $valuations = Valuation::where('update_method', '!=', ValuationUpdateMethod::FIXED)->where('update_method', '!=', ValuationUpdateMethod::MANUAL)->get();
        $valuations = Valuation::all();
        $histories = collect([]);

        foreach ($valuations as $valuation) {
            $initialValue = $valuation->value;
            $initialValueMainCurrency = $valuation->value_main_currency;
            for ($i = 0; $i < 365; $i++) {
                $percent = rand(-2, 2)  / 100;
                $newValue = $initialValue + ($initialValue * $percent);
                $newValueMainCurrency = $initialValueMainCurrency + ($initialValueMainCurrency * $percent);
                if (($valuation->update_method === ValuationUpdateMethod::FIXED) || ($valuation->update_method === ValuationUpdateMethod::MANUAL)) {
                    $newValue = $valuation->value;
                    $newValueMainCurrency = $valuation->value_main_currency;
                }
                $histories->push([
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'value' => $newValue,
                    'value_main_currency' => $newValueMainCurrency,
                    'user_id' => $user->id,
                    'valuation_id' => $valuation->id,
                    'created_at' => now()->subDays($i),
                    'updated_at' => now()->subDays($i),
                ]);
                $initialValue = $newValue;
                $initialValueMainCurrency = $newValueMainCurrency;
            }
        }
        $chunks = $histories->chunk(100);
        foreach ($chunks as $chunk) {
            ValuationHistory::insert($chunk->toArray());
        }
    }

    public function createTransactions(User $user)
    {
        $source = Asset::where('name', 'Boursorama Compte courant')->first();
        $destination = Asset::where('name', 'Microsoft')->first();

        Transaction::create([
            'type' => TransactionType::Transfer,
            'source_id' => $source->id,
            'destination_id' => $destination->id,
            'source_quantity' => 1200,
            'destination_quantity' => 3,
            'date' => now()->subMonths(3),
            'user_id' => $user->id
        ]);
        Transaction::create([
            'type' => TransactionType::Income,
            'destination_id' => $source->id,
            'destination_quantity' => 2200,
            'date' => now()->subMonths(4),
            'user_id' => $user->id
        ]);
    }
}
