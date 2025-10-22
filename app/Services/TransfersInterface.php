<?php

namespace App\Services;

use App\Models\Asset;
use App\Exceptions\CotationException;

interface TransfersInterface
{
    /**
     * Create a new cotation service instance.
     *
     * @param Cotation $cotation
     */
    public function __construct(Asset $asset);

    /**
     * Get the current transfers     
     * 
     * @return array
     * @throws CotationException
     */
    public function getTransfers(): array;

    /**
     * Get the fields for the asset.
     *
     * @return array
     */
    public static function getFields(): array;
}
