<?php

namespace App\Services;

use App\Models\Asset;
use App\Exceptions\CotationException;

interface AssetInterface
{
    /**
     * Create a new cotation service instance.
     *
     * @param Cotation $cotation
     */
    public function __construct(Asset $asset);

    /**
     * Get the current assets quantity     
     * 
     * @return float
     * @throws CotationException
     */
    public function getQuantity(): float;

    /**
     * Get the fields for the asset.
     *
     * @return array
     */
    public static function getFields(): array;
}
