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
        if (Schema::hasTable('vtiger_reportsortcol')) {
            return;
        }
        Schema::table('vtiger_reportsortcol', function (Blueprint $table) {
            $table->foreign(['reportid'], 'fk_1_vtiger_reportsortcol')->references(['reportid'])->on('vtiger_report')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_reportsortcol', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_reportsortcol');
        });
    }
};
