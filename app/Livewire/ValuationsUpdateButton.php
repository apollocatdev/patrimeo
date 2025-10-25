<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\SyncValuations;
use Illuminate\Support\Str;
use App\Data\UpdateValuationsStatus;
use App\Helpers\IntegrityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ValuationsUpdateButton extends Component
{
    public bool $isIntegrityValid = true;
    public array $status;
    public string $labelButton;
    public bool $shouldPoll;

    public function mount(): void
    {
        $this->status = UpdateValuationsStatus::get(Auth::id());
        $this->labelButton = $this->status['labelButton'] ?? __('Valuations');
        $this->isIntegrityValid = IntegrityHelper::isValid(Auth::id());
        $this->shouldPoll = $this->status['status'] === 'updating';
    }

    public function render()
    {
        return view('livewire.valuations-update-button');
    }

    public function refreshStatus()
    {
        $this->status = UpdateValuationsStatus::get(Auth::id());
        $this->labelButton = $this->status['labelButton'] ?? __('Valuations');
        $this->isIntegrityValid = IntegrityHelper::isValid(Auth::id());
        $this->shouldPoll = $this->status['status'] === 'updating';
    }

    public function updateValuations()
    {
        // Check if update is already in progress for this user
        if (UpdateValuationsStatus::get(Auth::id())['status'] === 'updating') {
            return;
        }

        // Start polling and dispatch the job
        $this->shouldPoll = true;
        SyncValuations::dispatch(null, Auth::id());

        // Update the status immediately to show "updating" state
        $this->status = UpdateValuationsStatus::update(Auth::id(), 'updating');
        $this->labelButton = $this->status['labelButton'];
    }
}
