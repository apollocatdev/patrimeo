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
        // Drop the old foreign key constraints that still reference 'cotations' table
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        // Recreate the foreign key constraints pointing to the new 'valuations' table
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('valuations');
        });

        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('valuations')->onDelete('cascade');
        });

        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('valuations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new foreign key constraints
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
        });

        // Recreate the old foreign key constraints (for rollback)
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('cotations');
        });

        Schema::table('valuation_updates', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('cotations')->onDelete('cascade');
        });

        Schema::table('valuation_histories', function (Blueprint $table) {
            $table->foreign('valuation_id')->references('id')->on('cotations')->onDelete('cascade');
        });
    }
};
