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
        if (Schema::hasTable('vtiger_reportmodules')) {
            return;
        }
        Schema::create('vtiger_reportmodules', function (Blueprint $table) {
            $table->integer('reportmodulesid')->primary();
            $table->string('primarymodule', 100)->nullable();
            $table->string('secondarymodules', 250)->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportmodules');
    }
};
