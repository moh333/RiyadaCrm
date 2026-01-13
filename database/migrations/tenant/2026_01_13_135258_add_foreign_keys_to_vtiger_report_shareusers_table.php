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
        Schema::table('vtiger_report_shareusers', function (Blueprint $table) {
            $table->foreign(['reportid'], 'vtiger_reports_reportid_ibfk_1')->references(['reportid'])->on('vtiger_report')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['userid'], 'vtiger_users_userid_ibfk_1')->references(['id'])->on('vtiger_users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_report_shareusers', function (Blueprint $table) {
            $table->dropForeign('vtiger_reports_reportid_ibfk_1');
            $table->dropForeign('vtiger_users_userid_ibfk_1');
        });
    }
};
