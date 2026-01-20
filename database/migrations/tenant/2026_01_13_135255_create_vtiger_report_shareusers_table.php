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
        if (Schema::hasTable('vtiger_report_shareusers')) {
            return;
        }
        Schema::create('vtiger_report_shareusers', function (Blueprint $table) {
            $table->integer('reportid')->index('vtiger_report_shareusers_ibfk_1');
            $table->integer('userid')->index('vtiger_users_userid_ibfk_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_report_shareusers');
    }
};
