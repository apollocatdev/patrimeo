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
        // Rename cotation_id to valuation_id in assets table
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('cotation_id', 'valuation_id');
        });

        // Rename cotation_id to valuation_id in valuation_updates table
        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->renameColumn('cotation_id', 'valuation_id');
        });

        // Rename cotation_id to valuation_id in valuation_histories table
        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->renameColumn('cotation_id', 'valuation_id');
        });

        // Rename transfer_id to transaction_id in transaction_taxonomy_tags table
        Schema::table('transaction_taxonomy_tags', function (Blueprint $table) {
            $table->renameColumn('transfer_id', 'transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Rename valuation_id back to cotation_id in assets table
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('valuation_id', 'cotation_id');
        });

        // Reverse: Rename valuation_id back to cotation_id in valuation_updates table
        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->renameColumn('valuation_id', 'cotation_id');
        });

        // Reverse: Rename valuation_id back to cotation_id in valuation_histories table
        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->renameColumn('valuation_id', 'cotation_id');
        });

        // Reverse: Rename transaction_id back to transfer_id in transaction_taxonomy_tags table
        Schema::table('transaction_taxonomy_tags', function (Blueprint $table) {
            $table->renameColumn('transaction_id', 'transfer_id');
        });
    }
};
