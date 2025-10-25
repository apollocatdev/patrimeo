<?php

namespace Database\Seeders;

use App\Models\User;
// Setting model removed - using filament-typehint-settings
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Database\Seeders\DemoSeeder;
use App\Enums\ValuationUpdateMethod;
use Illuminate\Support\Facades\Log;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'admin@test.dev',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        // Create default services settings for the user
        $this->createDefaultServicesSettings($user);

        // Create default currencies for the user
        $this->createDefaultCurrencies($user);

        // Create default security settings for the user
        $this->createDefaultSecuritySettings($user);

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }

    private function createDefaultServicesSettings(User $user): void
    {
        $defaultPeriodicity = [];

        foreach (ValuationUpdateMethod::cases() as $method) {
            // Skip FIXED method as it doesn't require updates
            if ($method === ValuationUpdateMethod::FIXED) {
                continue;
            }

            $defaultPeriodicity[$method->value] = 'hour';
            $defaultPeriodicity[$method->value . '_value'] = 6;
        }

        // Settings are now handled by filament-typehint-settings module
        // The settings will be created automatically when users access the settings
    }

    private function createDefaultCurrencies(User $user): void
    {
        \App\Models\Currency::insert([
            ['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['symbol' => 'USD', 'main' => false, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function createDefaultSecuritySettings(User $user): void
    {
        // Settings are now handled by filament-typehint-settings module
        // The settings will be created automatically when users access the settings
    }
}
