<?php

namespace App\Exceptions;

use Exception;
use App\Models\User;
use App\Models\Asset;
use App\Models\Cotation;
use App\Models\Notification;
use App\Models\CotationUpdate;
use App\Helpers\Logs\LogTransfers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransfersException extends Exception
{
    protected ?Asset $asset;
    protected ?int $httpStatusCode;
    protected ?string $httpError;
    protected ?array $data;

    public function __construct(
        ?Asset $asset,
        string $message,
        ?int $httpStatusCode = null,
        ?string $httpError = null,
        ?array $data = [],
    ) {
        parent::__construct($message);
        $this->asset = $asset;
        $this->httpStatusCode = $httpStatusCode;
        $this->httpError = $httpError;
        $this->data = $data;

        // Create notification for the user
        $this->createNotification();

        // Log the error
        $this->logError();
    }

    /**
     * Create a notification for the user about this exception
     */
    protected function createNotification(): void
    {
        $user = $this->asset ? $this->asset->user : (Auth::check() ? Auth::user() : null);
        if ($user) {
            Notification::createError(
                $user,
                'Transfers Update Error for' . $this->asset->name,
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
     * Log the error using LogTransfers
     */
    protected function logError(): void
    {
        $assetName = $this->asset ? $this->asset->name : 'Unknown';
        $message = "Transfers update error for {$assetName}: " . $this->getMessage();

        $context = [
            'asset_id' => $this->asset?->id,
            'asset_name' => $assetName,
            'http_status_code' => $this->httpStatusCode,
            'http_error' => $this->httpError,
            'data' => $this->data
        ];

        LogTransfers::error($message, $context);
    }
}
