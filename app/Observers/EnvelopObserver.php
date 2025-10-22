<?php

namespace App\Observers;

use App\Models\Envelop;
use App\Jobs\CheckIntegrity;

class EnvelopObserver
{
    /**
     * Handle the Envelop "created" event.
     */
    public function created(Envelop $envelop): void
    {
        CheckIntegrity::dispatch($envelop->user_id);
    }

    /**
     * Handle the Envelop "updated" event.
     */
    public function updated(Envelop $envelop): void
    {
        CheckIntegrity::dispatch($envelop->user_id);
    }

    /**
     * Handle the Envelop "deleted" event.
     */
    public function deleted(Envelop $envelop): void
    {
        CheckIntegrity::dispatch($envelop->user_id);
    }
}
