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
        Schema::create('cotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('isin')->nullable();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->float('value', 18, 6)->nullable();
            $table->float('value_main_currency', 18, 6)->nullable();
            $table->dateTime('last_update')->nullable();
            $table->string('update_method')->nullable();
            $table->json('update_data')->nullable();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotations');
    }
};
