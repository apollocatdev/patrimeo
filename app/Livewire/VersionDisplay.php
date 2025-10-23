<?php

namespace App\Livewire;

use Livewire\Component;

class VersionDisplay extends Component
{
    public string $version;

    public function mount()
    {
        // $this->version = config('custom.version', '0.0.0');
        $this->version = json_decode(file_get_contents(base_path('composer.json')), true)['version'] ?? 'dev';
    }

    public function render()
    {
        return view('livewire.version-display');
    }
}
