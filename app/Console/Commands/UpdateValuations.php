<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncValuations as SyncValuationsJob;

class UpdateValuations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-valuations {valuation?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all valuations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->argument('valuation') !== null) {
            $valuationNames = explode(',', $this->argument('valuation'));
            $valuationNames = array_map('trim', $valuationNames);
        }
        SyncValuationsJob::dispatchSync($valuationNames);
    }
}
