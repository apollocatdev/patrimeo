<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// Note: The old Setting model has been removed
// This command is for migrating from the old system to the new filament-typehint-settings system
use App\Models\User;
use App\Settings\LocalizationSettings;
use App\Settings\IntegrationsSettings;
use App\Settings\EmailSettings;
use App\Settings\VariousSettings;
use App\Settings\CotationUpdateSettings;
use App\Settings\AssetTransferSettings;
use Illuminate\Support\Facades\DB;

class MigrateSettingsCommand extends Command
{
    protected $signature = 'settings:migrate';
    protected $description = 'Migrate existing settings to the new filament-typehint-settings system';

    public function handle()
    {
        $this->info('Settings migration is no longer needed.');
        $this->info('The new filament-typehint-settings system automatically creates default settings for each user.');
        $this->info('Users can now access their settings through the Filament admin panel.');
        $this->info('');
        $this->info('If you had existing settings in the old system, they would need to be manually migrated.');
        $this->info('The old Setting model has been removed as part of this refactoring.');

        return 0;
    }
}
