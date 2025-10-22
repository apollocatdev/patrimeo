<?php

namespace App\Helpers\PortfolioCompute;


use App\Models\Transfer;
use App\Enums\TransferType;
use Illuminate\Support\Carbon;
use App\Models\CotationHistory;
use Illuminate\Support\Collection;
class Transfers
{
    public function __construct()
    {

    }

    public function getTransfersBetweenDates(Collection $assets, Carbon $date1, Carbon $date2): Collection
    {
        $assetIds = $assets->pluck('id');
        return Transfer::when($date1 !== null, function ($q) use ($date1) {
            return $q->where('date', '>=', $date1);
        })->when($date2 !== null, function ($q) use ($date2) {
            return $q->where('date', '<=', $date2);
        })->whereIn('source_id', $assetIds)->orWhereIn('destination_id', $assetIds)->get();
    }

    
    public function getTransferValue(Transfer $transfer)
    {
        if ($transfer->type === TransferType::Income) {
            $quantity = $transfer->destination_quantity;
            $cotation = $transfer->destination->cotation;
        }
        if ($transfer->type === TransferType::Expense) {
            $quantity = $transfer->source_quantity;
            $cotation = $transfer->source->cotation;
        }

        $cotation = CotationHistory::where('date', '<=', $transfer->date)->where('cotation_id', $cotation->id)->orderBy('date', 'desc')->first();
        if ($cotation === null) {
            return 0;
        }
        return $quantity * $cotation->value_main_currency;
    }
}