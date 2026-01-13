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
        Schema::table('vtiger_quotes', function (Blueprint $table) {
            $table->foreign(['potentialid'], 'fk_3_vtiger_quotes')->references(['potentialid'])->on('vtiger_potential')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['quoteid'], 'fk_crmid_vtiger_quotes')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_quotes', function (Blueprint $table) {
            $table->dropForeign('fk_3_vtiger_quotes');
            $table->dropForeign('fk_crmid_vtiger_quotes');
        });
    }
};
