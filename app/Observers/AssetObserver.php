<?php

namespace App\Observers;

use App\Models\Asset;
use App\Jobs\UpdateAllValues;
use App\Jobs\CheckIntegrity;
use App\Models\DashboardCache;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        UpdateAllValues::dispatchSync();
        $this->clearAllDashboardCache($asset);
        CheckIntegrity::dispatch($asset->user_id);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        UpdateAllValues::dispatchSync();
        $this->clearAllDashboardCache($asset);
        CheckIntegrity::dispatch($asset->user_id);
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        UpdateAllValues::dispatchSync();
        $this->clearAllDashboardCache($asset);
        CheckIntegrity::dispatch($asset->user_id);
    }

    /**
     * Clear cache for all dashboards since asset changes affect all dashboard data
     */
    protected function clearAllDashboardCache(Asset $asset): void
    {
        DashboardCache::where('user_id', $asset->user_id)->delete();
    }
}
