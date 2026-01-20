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
        if (Schema::hasTable('vtiger_reportsummary')) {
            return;
        }
        Schema::create('vtiger_reportsummary', function (Blueprint $table) {
            $table->integer('reportsummaryid')->index('reportsummary_reportsummaryid_idx');
            $table->integer('summarytype');
            $table->string('columnname', 250)->default('');

            $table->primary(['reportsummaryid', 'summarytype', 'columnname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportsummary');
    }
};
