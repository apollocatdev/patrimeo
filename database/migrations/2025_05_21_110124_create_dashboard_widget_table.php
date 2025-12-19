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
        Schema::create('dashboard_widget', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->foreignId('widget_id')->constrained('widgets')->onDelete('cascade');

            $table->unsignedInteger('sort')->nullable();
            $table->string('column_span')->nullable();
            $table->json('size')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboards_widgets');
    }
};
