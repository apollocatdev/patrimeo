<?php


namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Envelop;
use App\Models\Currency;
use App\Models\AssetClass;
use App\Models\EnvelopType;
use Illuminate\Database\Seeder;

class EnvelopClasses extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $this->createCurrencies($user);
        $this->createEnvelopTypes($user);
        $this->createEnvelops($user);
        $this->createAssetClasses($user);
    }

    public function createCurrencies(User $user)
    {
        Currency::insert([
            ['symbol' => 'EUR', 'main' => true, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['symbol' => 'USD', 'main' => false, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function createAssetClasses(User $user)
    {
        AssetClass::insert([
            ['name' => 'Cash', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Crypto', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Actions', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Obligations', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Real-estate', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Precious metal', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function createEnvelopTypes(User $user)
    {
        EnvelopType::insert([
            ['name' => 'Compte courant', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Physique', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Crypto', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PEA', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CTO', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hard-wallet', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DEX', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Assurance-Vie', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);  
    }
    public function createEnvelops(User $user)
    { 
        $types = EnvelopType::pluck('id', 'name');

        Envelop::insert([
            ['name' => 'SocGen', 'type_id' => $types['Compte courant'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Boursorama Banque', 'type_id' => $types['Compte courant'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ledger 1', 'type_id' => $types['Hard-wallet'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AAVE', 'type_id' => $types['DEX'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PEA Fortuneo', 'type_id' => $types['PEA'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Trade Republic', 'type_id' => $types['CTO'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Linxeo Suravenir', 'type_id' => $types['Assurance-Vie'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Domicile', 'type_id' => $types['Physique'], 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}