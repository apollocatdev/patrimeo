<x-filament-panels::page>
    <div x-data="{ tab: 'localization' }">
        <x-filament::tabs label="Settings">
            <x-filament::tabs.item @click="tab = 'localization'" x-bind:alpine-active="tab === 'localization'">
                Localization
            </x-filament::tabs.item>
            <x-filament::tabs.item @click="tab = 'integrations'" x-bind:alpine-active="tab === 'integrations'">
                Integrations
            </x-filament::tabs.item>
            <x-filament::tabs.item @click="tab = 'smtp'" x-bind:alpine-active="tab === 'smtp'">
                SMTP
            </x-filament::tabs.item>
            <x-filament::tabs.item @click="tab = 'various'" x-bind:alpine-active="tab === 'various'">
                Various
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-2">
            <div x-show="tab === 'localization'">
                <livewire:settings-localization />
            </div>
            <div x-show="tab === 'integrations'">
                <livewire:settings-integrations />
            </div>
            <div x-show="tab === 'smtp'">
                <livewire:settings-smtp />
            </div>
            <div x-show="tab === 'various'">
                <livewire:settings-various />
            </div>
        </div>
    </div>
</x-filament-panels::page>