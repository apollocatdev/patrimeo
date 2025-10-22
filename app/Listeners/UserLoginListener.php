<?php

namespace App\Listeners;

// Settings are now handled by filament-typehint-settings module
use Illuminate\Auth\Events\Login;
use App\Data\UpdateCotationsStatus;

class UserLoginListener
{
    public function handle(Login $event): void
    {
        // Settings are now handled by filament-typehint-settings module
        UpdateCotationsStatus::init($event->user->id);
    }
}
