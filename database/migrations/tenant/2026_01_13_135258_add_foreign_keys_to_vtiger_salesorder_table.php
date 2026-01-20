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
        if (Schema::hasTable('vtiger_salesorder')) {
            return;
        }
        Schema::table('vtiger_salesorder', function (Blueprint $table) {
            $table->foreign(['vendorid'], 'fk_3_vtiger_salesorder')->references(['vendorid'])->on('vtiger_vendor')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['salesorderid'], 'fk_crmid_vtiger_salesorder')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_salesorder', function (Blueprint $table) {
            $table->dropForeign('fk_3_vtiger_salesorder');
            $table->dropForeign('fk_crmid_vtiger_salesorder');
        });
    }
};
