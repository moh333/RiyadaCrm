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
        if (Schema::hasTable('vtiger_report')) {
            return;
        }
        Schema::table('vtiger_report', function (Blueprint $table) {
            $table->foreign(['queryid'], 'fk_2_vtiger_report')->references(['queryid'])->on('vtiger_selectquery')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_report', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_report');
        });
    }
};
