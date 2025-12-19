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
        Schema::create('cotation_updates', function (Blueprint $table) {
            $table->id();

            $table->dateTime('date');
            $table->string('status');
            $table->string('message')->nullable();
            $table->foreignId('cotation_id')->constrained('cotations')->onDelete('cascade');
            $table->float('value')->nullable();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotation_histories');
    }
};
