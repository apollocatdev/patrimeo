<?php

namespace App\Services;

use App\Models\Valuation;
use App\Exceptions\ValuationException;

interface ValuationInterface
{
    /**
     * Create a new valuation service instance.
     *
     * @param Valuation $valuation
     */
    public function __construct(Valuation $valuation);

    /**
     * Get the current quote/price for the valuation.
     *
     * @return float
     * @throws ValuationException
     */
    public function getQuote(): float;

    /**
     * Get the fields for the valuation.
     *
     * @return array
     */
    public static function getFields(): array;
}
