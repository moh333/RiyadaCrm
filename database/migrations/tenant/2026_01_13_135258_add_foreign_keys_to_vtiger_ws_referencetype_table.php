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
        if (Schema::hasTable('vtiger_ws_referencetype')) {
            return;
        }
        Schema::table('vtiger_ws_referencetype', function (Blueprint $table) {
            $table->foreign(['fieldtypeid'], 'fk_1_vtiger_referencetype')->references(['fieldtypeid'])->on('vtiger_ws_fieldtype')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_ws_referencetype', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_referencetype');
        });
    }
};
