<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Asset;
use App\Models\Taxonomy;
use App\Models\TaxonomyTag;
use Illuminate\Database\Seeder;

class Taxonomies extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $this->createClassTaxonomy($user);
        $this->createGeographicalTaxonomy($user);
    }

    public function createClassTaxonomy(User $user): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'Better asset classes',
            'weighted' => false,
            'user_id' => $user->id,
        ]);
        TaxonomyTag::insert([
            ['name' => 'Actions', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Obligations', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Real-estate', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Precious metal', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Crypto', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cash', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $mapAssets = [
            'SocGen compte courant' => 'Cash',
            'Boursorama Compte courant' => 'Cash',
            'Bitcoin' => 'Crypto',
            'USDC' => 'Cash',
            'Amundi MSCI World UCITS ETF' => 'Actions',
            'Amundi PEA MSCI Emerging Asia UCITS ETF' => 'Actions',
            'Cash PEA' => 'Cash',
            'Alphabet' => 'Actions',
            'Microsoft' => 'Actions',
            'Cash CTO' => 'Cash',
            'Linxea SURAVENIR' => 'Obligations',
            'EFImmo 1' => 'Real-estate',
            'PFO2' => 'Real-estate',
            'Gold - Napoleon 20 Frcs' => 'Precious metal',
        ];
        $taxonomyTags = TaxonomyTag::where('taxonomy_id', $taxonomy->id)->pluck('id', 'name');
        foreach ($mapAssets as $asset => $class) {
            $asset = Asset::where('name', $asset)->first();
            $asset->tags()->attach($taxonomyTags[$class], ['weight' => 1]);
        }
    }

    public function createGeographicalTaxonomy(User $user): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'Geographical dependencies',
            'weighted' => true,
            'user_id' => $user->id,
        ]);
        TaxonomyTag::insert([
            ['name' => 'US', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EU', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Asia', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Japan', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UK', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Middle East', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'South-America', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Africa', 'taxonomy_id' => $taxonomy->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $mapAssets = [
            'SocGen compte courant' => ['EU' => 1],
            'Boursorama Compte courant' => ['EU' => 1],
            'Bitcoin' => [],
            'USDC' => [],
            'Amundi MSCI World UCITS ETF' => ['US' => 0.72, 'Japan' => 0.05, 'UK' => 0.036, 'EU' => 0.01],
            'Amundi PEA MSCI Emerging Asia UCITS ETF' => ['Asia' => 1],
            'Cash PEA' => ['EU' => 1],
            'Alphabet' => ['US' => 1],
            'Microsoft' => ['US' => 1],
            'Cash CTO' => ['EU' => 1],
            'Linxea SURAVENIR' => ['EU' => 1],
            'EFImmo 1' => ['EU' => 1],
            'PFO2' => ['EU' => 1],
            'Gold - Napoleon 20 Frcs' => [],
        ];

        $taxonomyTags = TaxonomyTag::where('taxonomy_id', $taxonomy->id)->pluck('id', 'name');
        foreach ($mapAssets as $asset => $classes) {
            $asset = Asset::where('name', $asset)->first();
            foreach ($classes as $class => $weight) {
                $asset->tags()->attach($taxonomyTags[$class], ['weight' => $weight]);
            }
        }
    }
}
