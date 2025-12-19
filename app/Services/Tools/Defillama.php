<?php

namespace App\Services\Tools;

use App\Models\CryptoPool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Defillama
{
    protected CryptoPool $poolTracker;

    public function __construct(CryptoPool $poolTracker)
    {
        $this->poolTracker = $poolTracker;
    }

    public function updateApy(): void
    {
        $poolId = $this->poolTracker->update_data['pool_id'] ?? null;

        if (!$poolId) {
            throw new \Exception('Pool ID is required in update_data. Please configure it in the pool settings.');
        }

        $url = "https://yields.llama.fi/chart/{$poolId}";

        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch data from Defillama API. Status: {$response->status()}");
            }

            $data = $response->json();

            if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
                throw new \Exception('No data available from Defillama API');
            }

            // Get the last entry from the data array
            $lastEntry = end($data['data']);

            $apy = $lastEntry['apy'] ?? null;
            $utilization = $lastEntry['utilization'] ?? null;
            $liquidity = $lastEntry['tvlUsd'] ?? null;

            // Update the pool tracker
            $this->poolTracker->apy = $apy;
            $this->poolTracker->utilization_rate = $utilization !== null ? (int) round($utilization * 100) : null;
            $this->poolTracker->liquidity = $liquidity;
            $this->poolTracker->last_update = now();
            $this->poolTracker->save();
        } catch (\Exception $e) {
            Log::error('Defillama API error', [
                'pool_id' => $this->poolTracker->id,
                'pool_name' => $this->poolTracker->name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
