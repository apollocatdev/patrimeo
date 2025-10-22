<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\SyncCotations;
use Illuminate\Support\Str;
use App\Data\UpdateCotationsStatus;
use App\Helpers\IntegrityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CotationsUpdateButton extends Component
{
    public bool $isIntegrityValid = true;
    public array $status;
    public string $labelButton;
    public bool $shouldPoll;

    public function mount(): void
    {
        $this->status = UpdateCotationsStatus::get(Auth::id());
        $this->labelButton = $this->status['labelButton'];
        $this->isIntegrityValid = IntegrityHelper::isValid(Auth::id());
        $this->shouldPoll = $this->status['status'] === 'updating';
    }

    public function render()
    {
        return view('livewire.cotations-update-button');
    }

    public function refreshStatus()
    {
        $this->status = UpdateCotationsStatus::get(Auth::id());
        $this->labelButton = $this->status['labelButton'];
        $this->isIntegrityValid = IntegrityHelper::isValid(Auth::id());
        $this->shouldPoll = $this->status['status'] === 'updating';
    }

    public function updateCotations()
    {
        // Check if update is already in progress for this user
        if (UpdateCotationsStatus::get(Auth::id())['status'] === 'updating') {
            return;
        }

        // Start polling and dispatch the job
        $this->shouldPoll = true;
        SyncCotations::dispatch(null, Auth::id());

        // Update the status immediately to show "updating" state
        $this->status = UpdateCotationsStatus::update(Auth::id(), 'updating');
        $this->labelButton = $this->status['labelButton'];
    }
}
