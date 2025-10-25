<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\Currency;
use App\Services\Transfers\TransfersWoob;
use App\Services\Transfers\TransfersFinary;
use App\Services\Transfers\TransfersCommandJson;
use App\Services\Transfers\TransfersCommandSimpleBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransfersServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Auth::login($user);
        Currency::create(['symbol' => 'EUR', 'main' => true, 'user_id' => Auth::id()]);

        // Disable observers for testing
        Asset::unsetEventDispatcher();
    }

    public function test_command_simple_balance_creates_transfer(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $asset = Asset::create([
            'name' => 'Wallet',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 100,
            'last_update' => now()->subHour(),
            'user_id' => $userId,
            'update_data' => ['command' => 'php -r "echo 120;"'],
        ]);

        $service = new TransfersCommandSimpleBalance($asset);
        $transfers = $service->getTransfers();

        $this->assertCount(1, $transfers);
        $asset->refresh();
        $this->assertSame(120.0, (float) $asset->quantity);
    }

    public function test_command_json_creates_transfers(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $a1 = Asset::create([
            'name' => 'A1',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now()->subDays(2),
            'user_id' => $userId
        ]);
        $a2 = Asset::create([
            'name' => 'A2',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now()->subDays(2),
            'user_id' => $userId
        ]);

        $json = [
            ['type' => 'income', 'destination' => 'A1', 'destination_quantity' => 2.5, 'date' => now()->subHour()->toDateString()],
            ['type' => 'transfer', 'source' => 'A1', 'source_quantity' => 1.0, 'destination' => 'A2', 'destination_quantity' => 1.0, 'date' => now()->subHour()->toDateString()],
        ];

        $asset = $a1;
        // Create a temporary file with the JSON data
        $tempFile = tempnam(sys_get_temp_dir(), 'test_json_');
        file_put_contents($tempFile, json_encode($json));
        $asset->update(['update_data' => ['command' => 'cat ' . $tempFile]]);

        try {
            $service = new TransfersCommandJson($asset);
            $transfers = $service->getTransfers();

            $this->assertCount(2, $transfers);
            $a1->refresh();
            $this->assertSame(1.5, (float) $a1->quantity); // +2.5 -1.0
        } finally {
            // Clean up temporary file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_woob_parses_transactions_from_fake_binary(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        $asset = Asset::create([
            'name' => 'Checking',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 0,
            'last_update' => now()->subDays(2),
            'user_id' => $userId,
            'update_data' => ['account_name' => 'My Account', 'transaction_count' => 5],
        ]);

        // Use temporary directory instead of storage/app
        $scriptPath = tempnam(sys_get_temp_dir(), 'woob_fake_') . '.sh';
        $payload = json_encode([
            ['date' => now()->toDateString(), 'amount' => -10, 'label' => 'Coffee'],
            ['date' => now()->toDateString(), 'amount' => 20, 'label' => 'Refund'],
        ]);
        file_put_contents($scriptPath, "#!/usr/bin/env bash\necho " . escapeshellarg($payload) . "\n");
        chmod($scriptPath, 0755);

        // Use the new Settings framework to set the fake woob binary path
        $settings = \App\Settings\IntegrationsSettings::get();
        $settings->weboobPath = $scriptPath;
        $settings->save();

        try {
            $service = new TransfersWoob($asset);
            $transfers = $service->getTransfers();

            $this->assertCount(2, $transfers);
            $asset->refresh();
            $this->assertSame(10.0, (float) $asset->quantity); // -10 +20
        } finally {
            // Clean up temporary file
            if (file_exists($scriptPath)) {
                unlink($scriptPath);
            }
        }
    }

    public function test_finary_creates_transfer_from_difference(): void
    {
        $userId = Auth::id();

        // Create required related models
        $envelop = \App\Models\Envelop::factory()->create(['user_id' => $userId]);
        $assetClass = \App\Models\AssetClass::factory()->create(['user_id' => $userId]);

        // Configure settings used by Finary trait using the new Settings framework
        $settings = \App\Settings\IntegrationsSettings::get();
        $settings->weboobPath = env('WOOB_BINARY_PATH', '/usr/bin/woob');
        $settings->finarySharingLink = 'abc';
        $settings->finarySecureCode = 'xyz';
        $settings->save();

        // Settings are now handled by filament-typehint-settings module

        $asset = Asset::create([
            'name' => 'Crypto Wallet',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 1.0,
            'last_update' => now()->subDays(2),
            'user_id' => $userId,
            'update_data' => ['asset_type' => 'cryptos', 'object_id' => 'btc1'],
        ]);

        Http::fake([
            // cryptos endpoint
            'api.finary.com/users/me/portfolio/cryptos*' => Http::response([
                'result' => [
                    'accounts' => [[
                        'fiats' => [],
                        'securities' => [],
                        'cryptos' => [['id' => 'btc1', 'quantity' => 1.5, 'crypto' => ['name' => 'Bitcoin']]],
                        'fonds_euro' => [],
                        'precious_metals' => [],
                        'scpis' => [],
                    ]],
                ],
            ], 200),
        ]);

        $service = new TransfersFinary($asset);
        $transfers = $service->getTransfers();

        $this->assertCount(1, $transfers);
        $asset->refresh();
        $this->assertSame(1.5, (float) $asset->quantity);
    }
}
