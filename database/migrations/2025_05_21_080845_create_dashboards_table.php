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
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();

            $table->string('navigation_title');
            $table->string('navigation_icon')->nullable();
            $table->unsignedSmallInteger('navigation_sort_order')->default(0);
            $table->unsignedSmallInteger('n_columns')->default(2);

            $table->json('settings')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('default')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
