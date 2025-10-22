<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\CheckIntegrity;
use App\Models\User;
use App\Models\Asset;
use App\Models\Cotation;
use App\Models\Currency;
use App\Models\Envelop;
use App\Models\AssetClass;
use App\Helpers\IntegrityHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class CheckIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Currency $defaultCurrency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->defaultCurrency = Currency::create([
            'symbol' => 'EUR',
            'main' => true,
            'user_id' => $this->user->id
        ]);

        // Disable observers for testing to avoid UpdateAllValues job issues
        Asset::unsetEventDispatcher();
        Cotation::unsetEventDispatcher();
        Envelop::unsetEventDispatcher();
        AssetClass::unsetEventDispatcher();
    }

    public function test_check_integrity_job_stores_results_in_cache(): void
    {
        // Create some test data
        $envelop = Envelop::factory()->create(['user_id' => $this->user->id]);
        $assetClass = AssetClass::factory()->create(['user_id' => $this->user->id]);

        // Create an asset without cotation (should trigger alert)
        Asset::create([
            'name' => 'Test Asset',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 100,
            'user_id' => $this->user->id,
            'update_method' => 'manual'
        ]);

        // Run the integrity check
        $job = new CheckIntegrity($this->user->id);
        $job->handle();

        // Check that results are stored in cache
        $integrityData = IntegrityHelper::get($this->user->id);
        $this->assertNotNull($integrityData);
        $this->assertArrayHasKey('checks', $integrityData);

        // Check that the asset without cotation is detected
        $this->assertArrayHasKey('assets_without_cotation', $integrityData['checks']);
        $this->assertEquals(1, $integrityData['checks']['assets_without_cotation']['count']);
        $this->assertEquals('alert', $integrityData['checks']['assets_without_cotation']['level']);
    }

    public function test_check_integrity_job_detects_multiple_issues(): void
    {
        // Create test data
        $envelop = Envelop::factory()->create(['user_id' => $this->user->id]);
        $assetClass = AssetClass::factory()->create(['user_id' => $this->user->id]);

        // Create assets with various issues
        Asset::create([
            'name' => 'Asset without cotation',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 100,
            'user_id' => $this->user->id,
            'update_method' => 'manual'
        ]);

        Asset::create([
            'name' => 'Asset without quantity',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => null,
            'user_id' => $this->user->id,
            'update_method' => 'manual'
        ]);

        // Run the integrity check
        $job = new CheckIntegrity($this->user->id);
        $job->handle();

        // Check results
        $integrityData = IntegrityHelper::get($this->user->id);

        // The asset without quantity is also missing a cotation, so it's counted in both checks
        $this->assertEquals(2, $integrityData['checks']['assets_without_cotation']['count']);
        $this->assertEquals(1, $integrityData['checks']['assets_without_quantity']['count']);
        $this->assertEquals('alert', $integrityData['checks']['assets_without_cotation']['level']);
        $this->assertEquals('alert', $integrityData['checks']['assets_without_quantity']['level']);
    }

    public function test_check_integrity_job_with_clean_data(): void
    {
        // Create clean test data
        $envelop = Envelop::factory()->create(['user_id' => $this->user->id]);
        $assetClass = AssetClass::factory()->create(['user_id' => $this->user->id]);
        $cotation = Cotation::create([
            'name' => 'TEST',
            'currency_id' => $this->defaultCurrency->id,
            'user_id' => $this->user->id,
            'update_method' => 'manual'
        ]);

        Asset::create([
            'name' => 'Clean Asset',
            'envelop_id' => $envelop->id,
            'class_id' => $assetClass->id,
            'quantity' => 100,
            'cotation_id' => $cotation->id,
            'user_id' => $this->user->id,
            'update_method' => 'manual'
        ]);

        // Run the integrity check
        $job = new CheckIntegrity($this->user->id);
        $job->handle();

        // Check results
        $integrityData = IntegrityHelper::get($this->user->id);

        // All checks should have 0 count
        foreach ($integrityData['checks'] as $check) {
            $this->assertEquals(0, $check['count']);
        }
    }

    public function test_integrity_helper_is_valid_method(): void
    {
        // Test with no integrity data (should be valid)
        $this->assertTrue(IntegrityHelper::isValid($this->user->id));

        // Test with warnings only (should be valid)
        $integrityData = [
            'checks' => [
                'assets_without_envelop' => [
                    'level' => 'warning',
                    'count' => 1,
                    'items' => ['Asset1']
                ]
            ]
        ];
        IntegrityHelper::store($this->user->id, $integrityData);
        $this->assertTrue(IntegrityHelper::isValid($this->user->id));

        // Test with alerts (should be invalid)
        $integrityData['checks']['assets_without_cotation'] = [
            'level' => 'alert',
            'count' => 1,
            'items' => ['Asset2']
        ];
        IntegrityHelper::store($this->user->id, $integrityData);
        $this->assertFalse(IntegrityHelper::isValid($this->user->id));
    }
}
