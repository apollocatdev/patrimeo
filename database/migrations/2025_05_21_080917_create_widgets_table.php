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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
       

            // $table->string('name');
            // $table->string('type');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('type');

            // $table->json('input_data')->nullable();

            // $table->foreignId('widget_chart_id')->nullable()->constrained('widget_charts')->onDelete('cascade');
            // $table->unsignedBigInteger('widgetable_id')->nullable();
            // $table->string('widgetable_type')->nullable();

            // $table->unsignedInteger('polling')->nullable();

            $table->json('parameters')->nullable();
            $table->unsignedMediumInteger('sort')->default(0);
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
