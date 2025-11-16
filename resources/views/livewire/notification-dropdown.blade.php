{{-- NotificationDropdown Component - Using Filament Dropdown --}}
<x-filament::dropdown wire:poll.15s="loadNotifications" placement="bottom-end" width="lg" class="noblink">
    <x-slot name="trigger">
        <x-filament::button
            icon="heroicon-o-bell"
            class="!transition-none"
            outlined
            wire:loading.attr="none"
            :color="$notifications->count() > 0 ? 'warning' : 'success'"
            size="sm">
            <x-slot name="badge">
                {{ $notifications->count() > 99 ? '99+' : $notifications->count() }}
            </x-slot>
        </x-filament::button>
    </x-slot>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item>
            <div class="flex gap-x-4">
                <x-filament::link size="sm" color="info" weight="light" :href="route('filament.admin.resources.notifications.index')">
                    View all
                </x-filament::link>
                @if($notifications->count() > 0)
                <x-filament::link size="sm" weight="light" color="info" wire:click="markAllAsRead">
                    Mark all as read
                </x-filament::link>
                @endif
            </div>
        </x-filament::dropdown.list.item>
        <!-- Notifications List -->
        @forelse($notifications as $notification)
        <x-filament::dropdown.list.item
            icon="{{ $notification->type === 'success' ? 'heroicon-o-check-circle' : ($notification->type === 'warning' ? 'heroicon-o-exclamation-triangle' : ($notification->type === 'error' ? 'heroicon-o-x-circle' : 'heroicon-o-information-circle')) }}"
            icon-color="{{ $notification->type === 'success' ? 'success' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'error' ? 'danger' : 'primary')) }}"
            tag="a"
            :href="$notification->link">
            <div class="{{ $notification->read ? '' : 'font-semibold' }}">
                {{ $notification->title }}
            </div>
            <div>
                {{ $notification->message }}
            </div>
            <x-filament::badge color="gray">
                {{ $notification->created_at->diffForHumans() }}
            </x-filament::badge>
        </x-filament::dropdown.list.item>
        @empty
        <x-filament::dropdown.list.item icon="heroicon-o-bell">
            <span>No notifications</span>
        </x-filament::dropdown.list.item>
        @endforelse

    </x-filament::dropdown.list>
</x-filament::dropdown>