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
        if (Schema::hasTable('vtiger_pbxmanager_phonelookup')) {
            return;
        }
        Schema::table('vtiger_pbxmanager_phonelookup', function (Blueprint $table) {
            $table->foreign(['crmid'], 'vtiger_pbxmanager_phonelookup_ibfk_1')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_pbxmanager_phonelookup', function (Blueprint $table) {
            $table->dropForeign('vtiger_pbxmanager_phonelookup_ibfk_1');
        });
    }
};
