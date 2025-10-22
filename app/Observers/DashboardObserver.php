<?php

namespace App\Observers;

use App\Models\Dashboard;
use App\Models\DashboardCache;

class DashboardObserver
{

    protected function checkDashboard(Dashboard $dashboard)
    {
        if (! $dashboard->active) {
            $dashboard->default = false;
            $dashboard->saveQuietly();
            $defaultDashboard = Dashboard::where('user_id', $dashboard->user_id)->where('active', true)->first();
            if ($defaultDashboard) {
                $defaultDashboard->updateQuietly(['default' => true]);
            }
            return;
        }

        if ($dashboard->default) {
            Dashboard::where(['user_id' => $dashboard->user_id, 'default' => true])
                ->whereNot('id', $dashboard->id)
                ->get()
                ->each
                ->updateQuietly(['default' => false]);
            return;
        }

        if (! $dashboard->default && $dashboard->active) {
            $countDefault = Dashboard::where('user_id', $dashboard->user_id)->where('default', true)->whereNot('id', $dashboard->id)->count();
            if ($countDefault === 0) {
                $defaultDashboard = Dashboard::where('user_id', $dashboard->user_id)->where('active', true)->first();
                if ($defaultDashboard) {
                    $defaultDashboard->updateQuietly(['default' => true]);
                }
            }
        }
    }

    /**
     * Handle the Dashboard "created" event.
     */
    public function created(Dashboard $dashboard): void
    {
        $this->checkDashboard($dashboard);
    }



    /**
     * Handle the Dashboard "updated" event.
     */
    public function updated(Dashboard $dashboard): void
    {
        $this->checkDashboard($dashboard);

        // Clear cache when dashboard is updated
        DashboardCache::where('dashboard_id', $dashboard->id)->delete();
    }

    /**
     * Handle the Dashboard "deleted" event.
     */
    public function deleted(Dashboard $dashboard): void
    {
        $this->checkDashboard($dashboard);
    }
}
