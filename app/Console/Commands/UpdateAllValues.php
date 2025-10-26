<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAllValues as UpdateAllValuesJob;
use Illuminate\Console\Command;

class UpdateAllValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-values';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert and updates all valuations and assets values to the main currency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateAllValuesJob::dispatchSync();
    }
}
