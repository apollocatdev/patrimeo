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
        Schema::create('crypto_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('liquidity', 20, 6)->nullable();
            $table->float('apy', 6, 12)->nullable();
            $table->integer('utilization_rate')->nullable();
            $table->string('url')->nullable();
            $table->json('other_data')->nullable();
            $table->dateTime('last_update')->nullable();
            $table->string('update_method')->nullable();
            $table->json('update_data')->nullable();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_pools');
    }
};
