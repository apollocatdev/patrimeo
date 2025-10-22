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
    protected $signature = 'app:test-finary-importer';

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

        $service = new ImporterFinary(['sharing_link' => '00ac596e00a02460b354']);
        $data = $service->import();
        dd($data);
    }
}
