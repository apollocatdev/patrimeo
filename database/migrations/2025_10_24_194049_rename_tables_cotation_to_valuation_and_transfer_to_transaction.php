<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tables from Cotation to Valuation
        Schema::rename('cotations', 'valuations');
        Schema::rename('cotation_histories', 'valuation_histories');
        Schema::rename('cotation_updates', 'valuation_updates');

        // Rename tables from Transfer to Transaction
        Schema::rename('transfers', 'transactions');

        // Rename pivot table for transfers
        Schema::rename('transfer_taxonomy_tags', 'transaction_taxonomy_tags');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the table renames
        Schema::rename('valuations', 'cotations');
        Schema::rename('valuation_histories', 'cotation_histories');
        Schema::rename('valuation_updates', 'cotation_updates');

        Schema::rename('transactions', 'transfers');

        Schema::rename('transaction_taxonomy_tags', 'transfer_taxonomy_tags');
    }
};
