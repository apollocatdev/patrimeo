<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Demo\Assets;
use Database\Seeders\Demo\History;
use Database\Seeders\Demo\Valuations;
use Database\Seeders\Demo\Dashboards;
use Database\Seeders\Demo\Taxonomies;
use Database\Seeders\Demo\EnvelopClasses;
use App\Jobs\UpdateAllValues as UpdateAllValuesJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DemoSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $this->call([EnvelopClasses::class]);
        $this->call([Valuations::class]);
        $this->call([Assets::class]);
        $this->call([Taxonomies::class]);

        UpdateAllValuesJob::dispatchSync();

        $this->call([History::class]);
        $this->call([Dashboards::class]);
    }
}
