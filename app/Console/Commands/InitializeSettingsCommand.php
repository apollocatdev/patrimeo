<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Settings\LocalizationSettings;
use App\Settings\IntegrationsSettings;
use App\Settings\EmailSettings;
use App\Settings\VariousSettings;

class InitializeSettingsCommand extends Command
{
    protected $signature = 'settings:initialize';
    protected $description = 'Initialize all settings for all users';

    public function handle()
    {
        $this->info('Initializing settings for all users...');

        $users = User::all();
        $initializedCount = 0;

        foreach ($users as $user) {
            $this->info("Initializing settings for user: {$user->name} (ID: {$user->id})");

            // Set the authenticated user context for settings
            auth()->login($user);

            try {
                // Initialize all settings by calling get() on each one
                LocalizationSettings::get();
                IntegrationsSettings::get();
                EmailSettings::get();
                VariousSettings::get();

                $initializedCount++;
                $this->info("✓ Successfully initialized settings for user: {$user->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to initialize settings for user {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("Initialization completed! Initialized settings for {$initializedCount} users.");
    }
}
