<?php

namespace App\Observers;

use App\Models\Widget;
use App\Models\DashboardCache;

class WidgetObserver
{
    /**
     * Handle the Widget "created" event.
     */
    public function created(Widget $widget): void
    {
        $this->clearDashboardCache($widget);
    }

    /**
     * Handle the Widget "updated" event.
     */
    public function updated(Widget $widget): void
    {
        $this->clearDashboardCache($widget);
    }

    /**
     * Handle the Widget "deleted" event.
     */
    public function deleted(Widget $widget): void
    {
        $this->clearDashboardCache($widget);
    }

    /**
     * Clear cache for all dashboards that contain this widget
     */
    protected function clearDashboardCache(Widget $widget): void
    {
        // Get all dashboards that contain this widget
        $dashboards = $widget->dashboards;

        foreach ($dashboards as $dashboard) {
            DashboardCache::where('dashboard_id', $dashboard->id)->delete();
        }
    }
}
