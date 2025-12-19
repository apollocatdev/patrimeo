{{-- IntegrityDropdown Component - Using Filament Dropdown --}}
<div class="noblink">
    <x-filament::dropdown placement=" bottom-end" width="lg" wire:poll.15s="loadIntegrityStatus">
        <x-slot name="trigger">
            <x-filament::button
                outlined
                wire:loading.attr="none"
                icon="heroicon-o-shield-check"
                color="{{ count($alerts['alerts']) > 0 ? 'danger' : (count($alerts['warnings']) > 0 ? 'warning' : 'success') }}"
                size="sm">
                <x-slot name="badge">
                    @if(count($alerts['alerts']) > 0)
                    {{ count($alerts['alerts']) }}
                    @elseif(count($alerts['warnings']) > 0)
                    {{ count($alerts['warnings']) }}
                    @endif
                </x-slot>
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            <!-- Header -->
            <x-filament::dropdown.list.item icon="heroicon-o-shield-check">
                <div class="font-semibold">Data Integrity Status</div>
            </x-filament::dropdown.list.item>

            <!-- Check Results -->
            @if(count($alerts['alerts']) + count($alerts['warnings']) === 0)
            <x-filament::dropdown.list.item icon="heroicon-o-check-circle" icon-color="success">
                <span>All checks passed</span>
            </x-filament::dropdown.list.item>
            @else
            @foreach($alerts['alerts'] as $checkName => $check)
            <x-filament::dropdown.list.item
                icon="heroicon-o-x-circle"
                icon-color="danger">
                <div class="font-semibold">
                    {{ ucwords(str_replace('_', ' ', $checkName)) }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $check['count'] }} issue(s) found
                </div>
                @if(!empty($check['items']))
                @foreach($check['items'] as $item)
                <x-filament::dropdown.list.item icon="heroicon-o-minus" icon-color="gray" class="ml-4">
                    <span class="text-xs">{{ $item }}</span>
                </x-filament::dropdown.list.item>
                @endforeach
                @endif
            </x-filament::dropdown.list.item>
            @endforeach

            @foreach($alerts['warnings'] as $checkName => $check)
            <x-filament::dropdown.list.item
                icon="heroicon-o-exclamation-triangle"
                icon-color="warning">
                <div class="font-semibold">
                    {{ ucwords(str_replace('_', ' ', $checkName)) }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $check['count'] }} issue(s) found
                </div>
                @if(!empty($check['items']))
                @foreach($check['items'] as $item)
                <x-filament::dropdown.list.item icon="heroicon-o-minus" icon-color="gray" class="ml-4">
                    <span class="text-xs">{{ $item }}</span>
                </x-filament::dropdown.list.item>
                @endforeach
                @endif
            </x-filament::dropdown.list.item>
            @endforeach
            @endif
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>