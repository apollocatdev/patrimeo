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
        Schema::create('tags_assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('tag_id')->constrained('taxonomy_tags');
            $table->float('weight', 3, 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags_assets');
    }
};
