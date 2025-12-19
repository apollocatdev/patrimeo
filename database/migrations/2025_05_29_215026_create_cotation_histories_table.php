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
        Schema::create('cotation_histories', function (Blueprint $table) {
            $table->id();

            $table->date('date');

            $table->float('value', 18, 6);
            $table->float('value_main_currency', 18, 6);

            $table->foreignId('cotation_id')->constrained('cotations')->onDelete('cascade');
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
