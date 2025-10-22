<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncCotations as SyncCotationsJob;

class UpdateCotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-cotations {cotation?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all cotations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->argument('cotation') !== null) {
            $cotationNames = explode(',', $this->argument('cotation'));
            $cotationNames = array_map('trim', $cotationNames);
        }
        SyncCotationsJob::dispatchSync($cotationNames);
    }
}
