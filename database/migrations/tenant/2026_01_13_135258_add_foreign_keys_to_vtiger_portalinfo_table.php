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
        if (Schema::hasTable('vtiger_portalinfo')) {
            return;
        }
        Schema::table('vtiger_portalinfo', function (Blueprint $table) {
            $table->foreign(['id'], 'fk_1_vtiger_portalinfo')->references(['contactid'])->on('vtiger_contactdetails')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_portalinfo', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_portalinfo');
        });
    }
};
