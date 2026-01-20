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
        if (Schema::hasTable('vtiger_servicecontractscf')) {
            return;
        }
        Schema::table('vtiger_servicecontractscf', function (Blueprint $table) {
            $table->foreign(['servicecontractsid'], 'fk_servicecontractsid_vtiger_servicecontractscf')->references(['servicecontractsid'])->on('vtiger_servicecontracts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_servicecontractscf', function (Blueprint $table) {
            $table->dropForeign('fk_servicecontractsid_vtiger_servicecontractscf');
        });
    }
};
