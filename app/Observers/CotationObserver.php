<?php

namespace App\Observers;

use App\Models\Cotation;
use App\Jobs\UpdateAllValues;
use App\Jobs\CheckIntegrity;
use App\Models\DashboardCache;

class CotationObserver
{
    /**
     * Handle the Cotation "created" event.
     */
    public function created(Cotation $cotation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($cotation);
        CheckIntegrity::dispatch($cotation->user_id);
    }

    /**
     * Handle the Cotation "updated" event.
     */
    public function updated(Cotation $cotation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($cotation);
        CheckIntegrity::dispatch($cotation->user_id);
    }

    /**
     * Handle the Cotation "deleted" event.
     */
    public function deleted(Cotation $cotation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($cotation);
        CheckIntegrity::dispatch($cotation->user_id);
    }

    /**
     * Clear cache for all dashboards since cotation changes affect all dashboard data
     */
    protected function clearAllDashboardCache(Cotation $cotation): void
    {
        DashboardCache::where('user_id', $cotation->user_id)->delete();
    }
}
