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
        Schema::create('taxonomy_tags', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->unsignedBigInteger('taxonomy_id')->nullable();
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomy_tags');
    }
};
