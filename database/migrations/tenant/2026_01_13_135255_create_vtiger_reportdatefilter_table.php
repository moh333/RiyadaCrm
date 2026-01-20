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
        if (Schema::hasTable('vtiger_reportdatefilter')) {
            return;
        }
        Schema::create('vtiger_reportdatefilter', function (Blueprint $table) {
            $table->integer('datefilterid')->primary();
            $table->string('datecolumnname', 250)->nullable()->default('');
            $table->string('datefilter', 250)->nullable()->default('');
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();

            $table->index(['datefilterid'], 'reportdatefilter_datefilterid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportdatefilter');
    }
};
