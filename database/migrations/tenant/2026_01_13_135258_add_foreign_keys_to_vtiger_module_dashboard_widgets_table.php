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
        Schema::table('vtiger_module_dashboard_widgets', function (Blueprint $table) {
            $table->foreign(['dashboardtabid'], 'vtiger_module_dashboard_widgets_ibfk_1')->references(['id'])->on('vtiger_dashboard_tabs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_module_dashboard_widgets', function (Blueprint $table) {
            $table->dropForeign('vtiger_module_dashboard_widgets_ibfk_1');
        });
    }
};
