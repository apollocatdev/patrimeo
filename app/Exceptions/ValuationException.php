<?php

namespace App\Exceptions;

use Exception;
use App\Models\Valuation;
use App\Models\ValuationUpdate;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\Logs\LogValuations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ValuationException extends Exception
{
    protected ?Valuation $valuation;
    protected ?int $httpStatusCode;
    protected ?string $httpError;
    protected ?array $data;

    public function __construct(
        ?Valuation $valuation,
        string $message,
        ?int $httpStatusCode = null,
        ?string $httpError = null,
        ?array $data = [],
    ) {
        parent::__construct($message);
        $this->valuation = $valuation;
        $this->httpStatusCode = $httpStatusCode;
        $this->httpError = $httpError;
        $this->data = $data;

        // Create notification for the user
        $this->createNotification();
        $this->updateValuationUpdate();

        // Log the error
        $this->logError();
    }

    /**
     * Create a notification for the user about this exception
     */
    protected function createNotification(): void
    {
        $user = $this->valuation ? $this->valuation->user : (Auth::check() ? Auth::user() : null);
        if ($user) {
            Notification::createError(
                $user,
                'Valuation Update Error for ' . $this->valuation->name,
                $this->getMessage(),
                $this->data ?? []
            );
        }
    }


    public function updateValuationUpdate(): void
    {
        $valuationUpdate = ValuationUpdate::where('valuation_id', $this->valuation->id)->orderBy('date', 'desc')->first();

        if ($valuationUpdate) {
            $valuationUpdate->update([
                'status' => 'error',
                'message' => $this->getMessage(),
                'http_status_code' => $this->httpStatusCode,
                'error_details' => ['http_error' => $this->httpError],
                'date' => now(),
            ]);
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
     * Log the error using LogValuations
     */
    protected function logError(): void
    {
        $valuationName = $this->valuation ? $this->valuation->name : 'Unknown';
        $message = "Valuation update error for {$valuationName}: " . $this->getMessage();

        $context = [
            'valuation_id' => $this->valuation?->id,
            'valuation_name' => $valuationName,
            'http_status_code' => $this->httpStatusCode,
            'http_error' => $this->httpError,
            'data' => $this->data
        ];

        LogValuations::error($message, $context);
    }
}
