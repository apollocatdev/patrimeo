<?php

namespace App\Exceptions;

use Exception;
use App\Models\Cotation;
use App\Models\CotationUpdate;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\Logs\LogCotations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CotationException extends Exception
{
    protected ?Cotation $cotation;
    protected ?int $httpStatusCode;
    protected ?string $httpError;
    protected ?array $data;

    public function __construct(
        ?Cotation $cotation,
        string $message,
        ?int $httpStatusCode = null,
        ?string $httpError = null,
        ?array $data = [],
    ) {
        parent::__construct($message);
        $this->cotation = $cotation;
        $this->httpStatusCode = $httpStatusCode;
        $this->httpError = $httpError;
        $this->data = $data;

        // Create notification for the user
        $this->createNotification();
        $this->updateCotationUpdate();

        // Log the error
        $this->logError();
    }

    /**
     * Create a notification for the user about this exception
     */
    protected function createNotification(): void
    {
        $user = $this->cotation ? $this->cotation->user : (Auth::check() ? Auth::user() : null);
        if ($user) {
            Notification::createError(
                $user,
                'Cotation Update Error for ' . $this->cotation->name,
                $this->getMessage(),
                $this->data ?? []
            );
        }
    }


    public function updateCotationUpdate(): void
    {
        $cotationUpdate = CotationUpdate::where('cotation_id', $this->cotation->id)->orderBy('date', 'desc')->first();

        if ($cotationUpdate) {
            $cotationUpdate->update([
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
     * Log the error using LogCotations
     */
    protected function logError(): void
    {
        $cotationName = $this->cotation ? $this->cotation->name : 'Unknown';
        $message = "Cotation update error for {$cotationName}: " . $this->getMessage();

        $context = [
            'cotation_id' => $this->cotation?->id,
            'cotation_name' => $cotationName,
            'http_status_code' => $this->httpStatusCode,
            'http_error' => $this->httpError,
            'data' => $this->data
        ];

        LogCotations::error($message, $context);
    }
}
