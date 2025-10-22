<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\IntegrityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class IntegrityDropdown extends Component
{
    public bool $isOpen = false;
    public array $integrityStatus = [];
    public array $alerts = [];

    public function mount(): void
    {
        $this->loadIntegrityStatus();
    }

    public function toggleDropdown(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->loadIntegrityStatus();
        }
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
    }

    public function loadIntegrityStatus(): void
    {
        $this->integrityStatus = IntegrityHelper::get(Auth::id()) ?? [];

        $alerts = [
            'alerts' => [],
            'warnings' => []
        ];

        foreach ($this->integrityStatus['checks'] ?? [] as $checkName => $check) {
            if ($check['level'] === 'alert' && $check['count'] > 0) {
                $alerts['alerts'][$checkName] = $check;
            } elseif ($check['level'] === 'warning' && $check['count'] > 0) {
                $alerts['warnings'][$checkName] = $check;
            }
        }
        $this->alerts = $alerts;
    }

    public function render(): View
    {
        return view('livewire.integrity-dropdown');
    }
}
