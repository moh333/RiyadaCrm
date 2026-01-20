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
        if (Schema::hasTable('vtiger_datashare_module_rel')) {
            return;
        }
        Schema::create('vtiger_datashare_module_rel', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->integer('tabid')->index('idx_datashare_module_rel_tabid');
            $table->string('relationtype', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_module_rel');
    }
};
