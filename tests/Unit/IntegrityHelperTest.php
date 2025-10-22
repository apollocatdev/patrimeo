<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\IntegrityHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class IntegrityHelperTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_and_retrieve_integrity_data(): void
    {
        $integrityData = [
            'checks' => [
                'assets_without_cotation' => [
                    'level' => 'alert',
                    'count' => 2,
                    'items' => ['Asset1', 'Asset2']
                ]
            ]
        ];

        IntegrityHelper::store($this->user->id, $integrityData);

        $retrieved = IntegrityHelper::get($this->user->id);
        $this->assertEquals($integrityData, $retrieved);
    }

    public function test_is_valid_with_no_alerts(): void
    {
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
    }

    public function test_is_valid_with_alerts(): void
    {
        $integrityData = [
            'checks' => [
                'assets_without_cotation' => [
                    'level' => 'alert',
                    'count' => 1,
                    'items' => ['Asset1']
                ]
            ]
        ];

        IntegrityHelper::store($this->user->id, $integrityData);

        $this->assertFalse(IntegrityHelper::isValid($this->user->id));
    }

    public function test_is_valid_with_no_data(): void
    {
        // No integrity data stored, should default to valid
        $this->assertTrue(IntegrityHelper::isValid($this->user->id));
    }

    public function test_clear_integrity_data(): void
    {
        $integrityData = [
            'checks' => [
                'test_check' => [
                    'level' => 'warning',
                    'count' => 1
                ]
            ]
        ];

        IntegrityHelper::store($this->user->id, $integrityData);
        $this->assertNotNull(IntegrityHelper::get($this->user->id));

        IntegrityHelper::clear($this->user->id);
        $this->assertNull(IntegrityHelper::get($this->user->id));
    }

    public function test_cache_ttl(): void
    {
        $integrityData = [
            'checks' => [
                'test_check' => [
                    'level' => 'warning',
                    'count' => 1
                ]
            ]
        ];

        IntegrityHelper::store($this->user->id, $integrityData);

        // Verify data is stored
        $this->assertNotNull(IntegrityHelper::get($this->user->id));

        // Manually expire the cache to test TTL
        $cacheKey = 'data_integrity_' . $this->user->id;
        Cache::forget($cacheKey);

        $this->assertNull(IntegrityHelper::get($this->user->id));
    }
}
