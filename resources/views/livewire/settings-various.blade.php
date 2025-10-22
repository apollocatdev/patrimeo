<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="pt-12" style="padding-top: 2rem;">
            <div class="flex gap-3">
                <x-filament::button type="submit">
                    {{ __('Save settings') }}
                </x-filament::button>

            </div>
        </div>
    </form>
</div>