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
        Schema::table('vtiger_purchaseorder', function (Blueprint $table) {
            $table->foreign(['vendorid'], 'fk_4_vtiger_purchaseorder')->references(['vendorid'])->on('vtiger_vendor')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['purchaseorderid'], 'fk_crmid_vtiger_purchaseorder')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_purchaseorder', function (Blueprint $table) {
            $table->dropForeign('fk_4_vtiger_purchaseorder');
            $table->dropForeign('fk_crmid_vtiger_purchaseorder');
        });
    }
};
