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
        if (Schema::hasTable('vtiger_servicecf')) {
            return;
        }
        Schema::table('vtiger_servicecf', function (Blueprint $table) {
            $table->foreign(['serviceid'], 'fk_serviceid_vtiger_servicecf')->references(['serviceid'])->on('vtiger_service')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_servicecf', function (Blueprint $table) {
            $table->dropForeign('fk_serviceid_vtiger_servicecf');
        });
    }
};
