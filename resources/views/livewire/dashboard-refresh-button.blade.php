<div>
    @if($dashboardId)
    <x-filament::button
        badge-color="warning"
        icon="heroicon-m-arrow-path"
        outlined
        color="gray"
        size="sm"
        wire:click="refreshCurrentDashboard({{ $dashboardId }})">
        {{ __('Dashboard') }}
    </x-filament::button>
    @endif
</div>