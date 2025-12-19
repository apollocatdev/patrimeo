<?php

namespace App\Observers;

use App\Models\Valuation;
use App\Jobs\UpdateAllValues;
use App\Jobs\CheckIntegrity;
use App\Models\DashboardCache;

class ValuationObserver
{
    /**
     * Handle the Valuation "created" event.
     */
    public function created(Valuation $valuation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($valuation);
        CheckIntegrity::dispatch($valuation->user_id);
    }

    /**
     * Handle the Valuation "updated" event.
     */
    public function updated(Valuation $valuation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($valuation);
        CheckIntegrity::dispatch($valuation->user_id);
    }

    /**
     * Handle the Valuation "deleted" event.
     */
    public function deleted(Valuation $valuation): void
    {
        UpdateAllValues::dispatch();
        $this->clearAllDashboardCache($valuation);
        CheckIntegrity::dispatch($valuation->user_id);
    }

    /**
     * Clear cache for all dashboards since valuation changes affect all dashboard data
     */
    protected function clearAllDashboardCache(Valuation $valuation): void
    {
        DashboardCache::where('user_id', $valuation->user_id)->delete();
    }
}
