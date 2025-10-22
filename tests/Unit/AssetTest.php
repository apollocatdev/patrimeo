<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use App\Models\Transfer;
use App\Enums\TransferType;
use App\Enums\TransferUpdateMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetTest extends TestCase
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

    public function test_compute_quantity_updates_fields(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $assetA = Asset::create([
            'name' => 'Asset A',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 10,
            'last_update' => now()->subDays(2),
            'user_id' => $userId,
        ]);

        $assetB = Asset::create([
            'name' => 'Asset B',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now()->subDays(2),
            'user_id' => $userId,
        ]);

        // Older transfer (ignored)
        Transfer::create([
            'type' => TransferType::Income,
            'destination_id' => $assetA->id,
            'destination_quantity' => 5,
            'date' => now()->subDays(2),
            'user_id' => $userId,
        ]);

        // Relevant transfers (after last_update)
        $t1Date = now()->subHours(4);
        Transfer::create([
            'type' => TransferType::Income,
            'destination_id' => $assetA->id,
            'destination_quantity' => 3,
            'date' => $t1Date,
            'user_id' => $userId,
        ]);

        $t2Date = now()->subHours(2);
        Transfer::create([
            'type' => TransferType::Transfer,
            'source_id' => $assetA->id,
            'source_quantity' => 4,
            'destination_id' => $assetB->id,
            'destination_quantity' => 4,
            'date' => $t2Date,
            'user_id' => $userId,
        ]);

        $assetA->computeQuantity();
        $assetA->refresh();

        $this->assertSame(9.0, (float) $assetA->quantity); // 10 +3 -4
        $this->assertEquals($t2Date->toDateTimeString(), $assetA->last_update->toDateTimeString());
    }

    public function test_get_transfer_service_class(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $asset = Asset::create([
            'name' => 'Asset S',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now(),
            'user_id' => $userId,
        ]);

        $this->assertNull($asset->getTransferServiceClass());

        $asset->update(['update_method' => TransferUpdateMethod::COMMAND_JSON]);
        $this->assertNotNull($asset->getTransferServiceClass());
    }
}
