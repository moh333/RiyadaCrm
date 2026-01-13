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
        Schema::table('vtiger_role2picklist', function (Blueprint $table) {
            $table->foreign(['roleid'], 'fk_1_vtiger_role2picklist')->references(['roleid'])->on('vtiger_role')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['picklistid'], 'fk_2_vtiger_role2picklist')->references(['picklistid'])->on('vtiger_picklist')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_role2picklist', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_role2picklist');
            $table->dropForeign('fk_2_vtiger_role2picklist');
        });
    }
};
