<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex gap-3 mt-6">
            <x-filament::button type="submit">
                Save settings
            </x-filament::button>

            <x-filament::button
                type="button"
                color="danger"
                wire:click="clearAllCache"
                wire:confirm="Are you sure you want to clear all dashboard cache?">
                Clear all cache
            </x-filament::button>
        </div>
    </form>
</div>