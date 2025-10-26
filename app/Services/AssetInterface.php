<?php

namespace App\Services;

use App\Models\Asset;
use App\Exceptions\ValuationException;

interface AssetInterface
{
    /**
     * Create a new valuation service instance.
     *
     * @param Valuation $valuation
     */
    public function __construct(Asset $asset);

    /**
     * Get the current assets quantity     
     * 
     * @return float
     * @throws ValuationException
     */
    public function getQuantity(): float;

    /**
     * Get the fields for the asset.
     *
     * @return array
     */
    public static function getFields(): array;
}
