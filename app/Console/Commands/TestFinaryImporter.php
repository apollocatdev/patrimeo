<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\ImporterFinary;

class TestFinaryImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-finary-importer {sharing_link}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sharingLink = $this->argument('sharing_link');

        $service = new ImporterFinary(['sharing_link' => $sharingLink]);
        $data = $service->import();
        dd($data);
    }
}
