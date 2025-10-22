<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Dashboard;
use App\Models\DashboardCache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\UserDashboard;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;
use App\Helpers\Logs\LogDashboards;

class DashboardRefreshButton extends Component
{
    public function refreshCurrentDashboard(?int $dashboardId): void
    {

        if ($dashboardId) {
            $dashboard = Dashboard::find($dashboardId);

            if ($dashboard) {
                $widgetCount = $dashboard->widgets->count();
                foreach ($dashboard->widgets as $widget) {
                    DashboardCache::where('dashboard_widget_id', $widget->id)
                        ->where('user_id', Auth::id())
                        ->delete();
                }

                // Log manual cache refresh
                LogDashboards::info("Dashboard cache manually refreshed: {$dashboard->navigation_title} ({$widgetCount} widgets)");

                Notification::make()
                    ->title('Dashboard cache cleared')
                    ->success()
                    ->send();

                $this->redirect(UserDashboard::getUrl(['dashboardId' => $dashboardId]));
            }
        } else {
            Log::error('Dashboard ID not found in URL: ' . request()->url());
        }
    }

    public function render()
    {
        $dashboardId = request()->route('dashboardId') ?? null;

        return view('livewire.dashboard-refresh-button', [
            'dashboardId' => $dashboardId,
        ]);
    }
}
