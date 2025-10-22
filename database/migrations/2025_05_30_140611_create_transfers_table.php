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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            // Expense, Transfer, Income
            $table->string('type');

            $table->foreignId('source_id')->nullable()->constrained('assets')->onDelete('cascade');
            $table->float('source_quantity', 12, 4)->nullable();

            $table->foreignId('destination_id')->nullable()->constrained('assets')->onDelete('cascade');
            $table->float('destination_quantity', 12, 4)->nullable();

            // $table->float('quantity', 12, 4);
            // $table->float('value', 12, 4)->nullable();
            // $table->float('value_main_currency', 12, 4);
            // $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');

            $table->date('date');

            $table->string('comment')->nullable();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
