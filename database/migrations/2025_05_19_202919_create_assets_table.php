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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->foreignId('envelop_id')->constrained('envelops');
            $table->foreignId('class_id')->constrained('asset_classes');

            $table->float('quantity', 8, 2)->nullable();
            $table->foreignId('cotation_id')->nullable()->constrained('cotations');

            $table->float('value', 8, 2)->nullable();

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
        Schema::dropIfExists('assets');
    }
};
