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
        if (Schema::hasTable('vtiger_import_locks')) {
            return;
        }
        Schema::create('vtiger_import_locks', function (Blueprint $table) {
            $table->integer('vtiger_import_lock_id')->primary();
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('importid');
            $table->dateTime('locked_since')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_import_locks');
    }
};
