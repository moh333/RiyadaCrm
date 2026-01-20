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
        if (Schema::hasTable('vtiger_servicecontracts')) {
            return;
        }
        Schema::table('vtiger_servicecontracts', function (Blueprint $table) {
            $table->foreign(['servicecontractsid'], 'fk_crmid_vtiger_servicecontracts')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_servicecontracts', function (Blueprint $table) {
            $table->dropForeign('fk_crmid_vtiger_servicecontracts');
        });
    }
};
