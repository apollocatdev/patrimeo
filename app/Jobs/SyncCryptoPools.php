<?php

namespace App\Jobs;

use Exception;
use App\Models\CryptoPool;
use App\Helpers\Logs\LogTools;
use App\Exceptions\ToolsException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncCryptoPools implements ShouldQueue
{
    use Queueable;

    protected ?array $cryptoPoolNames;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(?array $cryptoPoolNames = null, ?int $userId = null)
    {
        $this->cryptoPoolNames = $cryptoPoolNames;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $query = CryptoPool::query();
        $query->whereIn('name', $this->cryptoPoolNames);
        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        }
        $cryptoPools = $query->get();
        foreach ($cryptoPools as $cryptoPool) {
            $this->updateCryptoPool($cryptoPool);
        }
    }

    public function updateCryptoPool(CryptoPool $cryptoPool): void
    {
        $class = $cryptoPool->update_method->getServiceClass();
        if ($class !== null) {
            try {
                (new $class($cryptoPool))->updateApy();
                LogTools::info("Updated APY for crypto pool {$cryptoPool->name}");
            } catch (Exception $e) {
                throw new ToolsException($cryptoPool, $e->getMessage(), null, null, ['type' => 'crypto_pool_update']);
            }
        }
    }
}
