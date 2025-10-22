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
        Schema::table('cotation_updates', function (Blueprint $table) {
            $table->integer('http_status_code')->nullable()->after('message');
            $table->json('error_details')->nullable()->after('http_status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotation_updates', function (Blueprint $table) {
            $table->dropColumn(['http_status_code', 'error_details']);
        });
    }
};
