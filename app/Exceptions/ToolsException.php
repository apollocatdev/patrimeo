<?php

namespace App\Exceptions;

use Exception;
use App\Models\CryptoPool;
use App\Models\Notification;
use App\Helpers\Logs\LogTools;
use Illuminate\Support\Facades\Auth;

class ToolsException extends Exception
{
    protected ?CryptoPool $cryptoPool;
    protected ?int $httpStatusCode;
    protected ?string $httpError;
    protected ?array $data;

    public function __construct(
        ?CryptoPool $cryptoPool,
        string $message,
        ?int $httpStatusCode = null,
        ?string $httpError = null,
        ?array $data = [],
    ) {
        parent::__construct($message);
        $this->cryptoPool = $cryptoPool;
        $this->httpStatusCode = $httpStatusCode;
        $this->httpError = $httpError;
        $this->data = $data;

        // Create notification for the user
        if ($this->cryptoPool) {
            $this->createNotificationCryptoPool();

            // Log the error
            $this->logErrorCryptoPool();
        }
    }

    /**
     * Create a notification for the user about this exception
     */
    protected function createNotificationCryptoPool(): void
    {
        $user = $this->cryptoPool ? $this->cryptoPool->user : (Auth::check() ? Auth::user() : null);
        if ($user) {
            Notification::createError(
                $user,
                'Crypto Pool Tool error for' . $this->cryptoPool->name,
                $this->getMessage(),
                $this->data ?? []
            );
        }
    }


    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getHttpError(): ?string
    {
        return $this->httpError;
    }

    public function getFullMessage(): string
    {
        $message = $this->getMessage();
        if ($this->httpStatusCode) {
            $message .= "\n\nHTTP Status: " . $this->httpStatusCode;
        }
        if ($this->httpError) {
            $message .= "\nError Details:\n" . $this->httpError;
        }
        return $message;
    }

    /**
     * Log the error using LogTransactions
     */
    protected function logErrorCryptoPool(): void
    {
        $cryptoPoolName = $this->cryptoPool ? $this->cryptoPool->name : 'Unknown';
        $message = "Crypto Pool Tool error for {$cryptoPoolName}: " . $this->getMessage();

        $context = [
            'crypto_pool_id' => $this->cryptoPool?->id,
            'crypto_pool_name' => $cryptoPoolName,
            'http_status_code' => $this->httpStatusCode,
            'http_error' => $this->httpError,
            'data' => $this->data
        ];

        LogTools::error($message, $context);
    }
}
