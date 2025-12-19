<?php

namespace App\Observers;

use App\Models\Currency;
use App\Jobs\CheckIntegrity;

class CurrencyObserver
{
    /**
     * Handle the Currency "created" event.
     */
    public function created(Currency $currency): void
    {
        CheckIntegrity::dispatch($currency->user_id);
    }

    /**
     * Handle the Currency "updated" event.
     */
    public function updated(Currency $currency): void
    {
        CheckIntegrity::dispatch($currency->user_id);
    }

    /**
     * Handle the Currency "deleted" event.
     */
    public function deleted(Currency $currency): void
    {
        CheckIntegrity::dispatch($currency->user_id);
    }
}
