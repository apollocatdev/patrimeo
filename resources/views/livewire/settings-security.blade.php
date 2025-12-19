<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Save Security Settings
            </x-filament::button>
        </div>
    </form>
</div>