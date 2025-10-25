<?php

namespace App\Services;

use App\Models\Asset;
use App\Exceptions\TransactionsException;

interface TransactionsInterface
{
    /**
     * Create a new transaction service instance.
     *
     * @param Asset $asset
     */
    public function __construct(Asset $asset);

    /**
     * Get the current transactions     
     * 
     * @return array
     * @throws TransactionsException
     */
    public function getTransactions(): array;

    /**
     * Get the fields for the asset.
     *
     * @return array
     */
    public static function getFields(): array;
}
