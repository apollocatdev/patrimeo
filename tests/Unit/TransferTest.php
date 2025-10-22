<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use App\Models\Transfer;
use App\Enums\TransferType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Auth::login($user);

        // Disable observers for testing
        Asset::unsetEventDispatcher();
    }

    public function test_check_duplicate_detects_existing(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $asset = Asset::create([
            'name' => 'Main',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now(),
            'user_id' => $userId,
        ]);

        $date = now();
        $existing = Transfer::create([
            'type' => TransferType::Income,
            'destination_id' => $asset->id,
            'destination_quantity' => 10,
            'date' => $date,
            'user_id' => $userId,
        ]);

        $candidate = new Transfer([
            'type' => TransferType::Income,
            'destination_id' => $asset->id,
            'destination_quantity' => 10,
            'date' => $date,
            'user_id' => $userId,
        ]);

        $this->assertTrue($candidate->checkDuplicate());

        // When excluding itself
        $existing->refresh();
        $this->assertFalse($existing->checkDuplicate());
    }
}
