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
        if (Schema::hasTable('vtiger_scheduled_reports')) {
            return;
        }
        Schema::create('vtiger_scheduled_reports', function (Blueprint $table) {
            $table->integer('reportid')->primary();
            $table->text('recipients')->nullable();
            $table->text('schedule')->nullable();
            $table->string('format', 10)->nullable();
            $table->timestamp('next_trigger_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_scheduled_reports');
    }
};
