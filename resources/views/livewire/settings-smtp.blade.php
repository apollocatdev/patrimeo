<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                {{ __('Save Settings') }}
            </x-filament::button>
        </div>
    </form>
</div>