<?php

namespace App\Services;

use App\Models\Cotation;
use App\Exceptions\CotationException;

interface CotationInterface
{
    /**
     * Create a new cotation service instance.
     *
     * @param Cotation $cotation
     */
    public function __construct(Cotation $cotation);

    /**
     * Get the current quote/price for the cotation.
     *
     * @return float
     * @throws CotationException
     */
    public function getQuote(): float;

    /**
     * Get the fields for the cotation.
     *
     * @return array
     */
    public static function getFields(): array;
}
