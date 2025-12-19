<?php

namespace App\Listeners;

// Settings are now handled by filament-typehint-settings module
use Illuminate\Auth\Events\Login;
use App\Data\UpdateValuationsStatus;

class UserLoginListener
{
    public function handle(Login $event): void
    {
        // Settings are now handled by filament-typehint-settings module
        UpdateValuationsStatus::init($event->user->id);
    }
}
