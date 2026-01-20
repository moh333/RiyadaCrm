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
        if (Schema::hasTable('vtiger_tracker')) {
            return;
        }
        Schema::create('vtiger_tracker', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('user_id', 36)->nullable();
            $table->string('module_name', 25)->nullable();
            $table->string('item_id', 36)->nullable();
            $table->string('item_summary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tracker');
    }
};
