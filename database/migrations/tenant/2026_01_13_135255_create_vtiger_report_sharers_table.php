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
        if (Schema::hasTable('vtiger_report_sharers')) {
            return;
        }
        Schema::create('vtiger_report_sharers', function (Blueprint $table) {
            $table->integer('reportid')->index('vtiger_report_sharers_ibfk_1');
            $table->string('rsid')->index('vtiger_rolesd_rsid_ibfk_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_report_sharers');
    }
};
