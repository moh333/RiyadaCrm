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
        Schema::create('vtiger_google_sync_settings', function (Blueprint $table) {
            $table->integer('user')->nullable();
            $table->string('module', 50)->nullable();
            $table->string('clientgroup')->nullable();
            $table->string('direction', 50)->nullable();
            $table->tinyInteger('enabled')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_google_sync_settings');
    }
};
