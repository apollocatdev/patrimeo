<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            System Logs
        </x-slot>

        <x-slot name="description">
            View system logs for different components. Log levels can be configured in the Various settings.
        </x-slot>

        <x-filament::tabs>
            <x-filament::tabs.item wire:click="$set('selectedChannel', 'valuations')" :active="$selectedChannel === 'valuations'">
                Valuations
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('selectedChannel', 'transactions')" :active="$selectedChannel === 'transactions'">
                Transactions
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('selectedChannel', 'dashboards')" :active="$selectedChannel === 'dashboards'">
                Dashboards
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('selectedChannel', 'scheduler')" :active="$selectedChannel === 'scheduler'">
                Scheduler
            </x-filament::tabs.item>

            <x-filament::tabs.item wire:click="$set('selectedChannel', 'tools')" :active="$selectedChannel === 'tools'">
                Tools
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-6">
            <div class="mb-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <strong>Current log file:</strong> {{ $selectedChannel }}.log
                </div>
                <x-filament::button
                    wire:click="clearAllLogs"
                    color="danger"
                    icon="heroicon-o-trash"
                    wire:confirm="Are you sure you want to clear all log files? This action cannot be undone."
                    size="sm">
                    Clear All Log Files
                </x-filament::button>
            </div>
            <textarea wire:model="logContent" class="font-mono text-sm w-full h-96 p-4 border rounded" readonly>{{ $logContent }}</textarea>
        </div>
    </x-filament::section>
</x-filament-panels::page>