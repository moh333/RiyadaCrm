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
        if (Schema::hasTable('vtiger_settings_blocks')) {
            return;
        }
        Schema::create('vtiger_settings_blocks', function (Blueprint $table) {
            $table->integer('blockid')->primary();
            $table->string('label', 250)->nullable();
            $table->integer('sequence')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_settings_blocks');
    }
};
