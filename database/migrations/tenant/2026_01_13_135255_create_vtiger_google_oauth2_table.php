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
        if (Schema::hasTable('vtiger_google_oauth2')) {
            return;
        }
        Schema::create('vtiger_google_oauth2', function (Blueprint $table) {
            $table->string('service', 20)->nullable();
            $table->string('access_token', 500)->nullable();
            $table->string('refresh_token', 500)->nullable();
            $table->integer('userid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_google_oauth2');
    }
};
