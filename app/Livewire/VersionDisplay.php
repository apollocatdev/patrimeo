<?php

namespace App\Livewire;

use Livewire\Component;

class VersionDisplay extends Component
{
    public string $version;

    public function mount()
    {
        $this->version = config('custom.version', '0.0.0');
    }

    public function render()
    {
        return view('livewire.version-display');
    }
}
