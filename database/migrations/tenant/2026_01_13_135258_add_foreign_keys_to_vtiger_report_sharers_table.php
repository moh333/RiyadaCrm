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
        Schema::table('vtiger_report_sharers', function (Blueprint $table) {
            $table->foreign(['reportid'], 'vtiger_report_reportid_ibfk_4')->references(['reportid'])->on('vtiger_report')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['rsid'], 'vtiger_rolesd_rsid_ibfk_1')->references(['roleid'])->on('vtiger_role')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_report_sharers', function (Blueprint $table) {
            $table->dropForeign('vtiger_report_reportid_ibfk_4');
            $table->dropForeign('vtiger_rolesd_rsid_ibfk_1');
        });
    }
};
