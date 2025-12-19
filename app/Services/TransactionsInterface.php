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
     * Save the current transactions     
     * 
     * @return void
     * @throws TransactionsException
     */
    public function saveTransactions(): void;

    /**
     * Get the fields for the asset.
     *
     * @return array
     */
    public static function getFields(): array;
}
