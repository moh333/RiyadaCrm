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
        if (Schema::hasTable('vtiger_report_sharegroups')) {
            return;
        }
        Schema::table('vtiger_report_sharegroups', function (Blueprint $table) {
            $table->foreign(['groupid'], 'vtiger_groups_groupid_ibfk_1')->references(['groupid'])->on('vtiger_groups')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['reportid'], 'vtiger_report_reportid_ibfk_2')->references(['reportid'])->on('vtiger_report')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_report_sharegroups', function (Blueprint $table) {
            $table->dropForeign('vtiger_groups_groupid_ibfk_1');
            $table->dropForeign('vtiger_report_reportid_ibfk_2');
        });
    }
};
