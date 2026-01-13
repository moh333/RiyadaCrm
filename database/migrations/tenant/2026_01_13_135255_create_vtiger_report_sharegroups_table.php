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
        Schema::create('vtiger_report_sharegroups', function (Blueprint $table) {
            $table->integer('reportid')->index('vtiger_report_sharegroups_ibfk_1');
            $table->integer('groupid')->index('vtiger_groups_groupid_ibfk_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_report_sharegroups');
    }
};
