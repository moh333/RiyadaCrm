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
        if (Schema::hasTable('vtiger_customerportal_tabs')) {
            return;
        }
        Schema::create('vtiger_customerportal_tabs', function (Blueprint $table) {
            $table->integer('tabid')->primary();
            $table->integer('visible')->nullable()->default(1);
            $table->integer('sequence')->nullable();
            $table->boolean('createrecord')->default(false);
            $table->boolean('editrecord')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customerportal_tabs');
    }
};
