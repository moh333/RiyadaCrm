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
        if (Schema::hasTable('vtiger_projectmilestone')) {
            return;
        }
        Schema::table('vtiger_projectmilestone', function (Blueprint $table) {
            $table->foreign(['projectmilestoneid'], 'fk_crmid_vtiger_projectmilestone')->references(['crmid'])->on('vtiger_crmentity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_projectmilestone', function (Blueprint $table) {
            $table->dropForeign('fk_crmid_vtiger_projectmilestone');
        });
    }
};
