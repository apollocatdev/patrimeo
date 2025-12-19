<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('filament-settings.table_name', 'settings');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->json('settings')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['class', 'user_id']);
            $table->index(['class', 'user_id']);
        });
    }
};
