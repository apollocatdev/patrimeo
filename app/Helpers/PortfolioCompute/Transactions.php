<?php

namespace App\Helpers\PortfolioCompute;


use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Carbon;
use App\Models\ValuationHistory;
use Illuminate\Support\Collection;

class Transactions
{
    public function __construct() {}

    public function getTransactionsBetweenDates(Collection $assets, Carbon $date1, Carbon $date2): Collection
    {
        $assetIds = $assets->pluck('id');
        return Transaction::when($date1 !== null, function ($q) use ($date1) {
            return $q->where('date', '>=', $date1);
        })->when($date2 !== null, function ($q) use ($date2) {
            return $q->where('date', '<=', $date2);
        })->whereIn('source_id', $assetIds)->orWhereIn('destination_id', $assetIds)->get();
    }


    public function getTransactionValue(Transaction $transaction)
    {
        if ($transaction->type === TransactionType::Income) {
            $quantity = $transaction->destination_quantity;
            $valuation = $transaction->destination->valuation;
        }
        if ($transaction->type === TransactionType::Expense) {
            $quantity = $transaction->source_quantity;
            $valuation = $transaction->source->valuation;
        }

        $valuation = ValuationHistory::where('date', '<=', $transaction->date)->where('valuation_id', $valuation->id)->orderBy('date', 'desc')->first();
        if ($valuation === null) {
            return 0;
        }
        return $quantity * $valuation->value_main_currency;
    }
}
