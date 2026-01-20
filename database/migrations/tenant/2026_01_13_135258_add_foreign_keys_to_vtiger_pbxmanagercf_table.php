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
        if (Schema::hasTable('vtiger_pbxmanagercf')) {
            return;
        }
        Schema::table('vtiger_pbxmanagercf', function (Blueprint $table) {
            $table->foreign(['pbxmanagerid'], 'fk_pbxmanagerid_vtiger_pbxmanagercf')->references(['pbxmanagerid'])->on('vtiger_pbxmanager')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_pbxmanagercf', function (Blueprint $table) {
            $table->dropForeign('fk_pbxmanagerid_vtiger_pbxmanagercf');
        });
    }
};
