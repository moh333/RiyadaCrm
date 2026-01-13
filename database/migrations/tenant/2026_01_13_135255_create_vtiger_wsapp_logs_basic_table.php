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
        Schema::create('vtiger_wsapp_logs_basic', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('extensiontabid')->nullable();
            $table->string('module', 50);
            $table->dateTime('sync_datetime');
            $table->integer('app_create_count')->nullable();
            $table->integer('app_update_count')->nullable();
            $table->integer('app_delete_count')->nullable();
            $table->integer('app_skip_count')->nullable();
            $table->integer('vt_create_count')->nullable();
            $table->integer('vt_update_count')->nullable();
            $table->integer('vt_delete_count')->nullable();
            $table->integer('vt_skip_count')->nullable();
            $table->integer('userid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_logs_basic');
    }
};
