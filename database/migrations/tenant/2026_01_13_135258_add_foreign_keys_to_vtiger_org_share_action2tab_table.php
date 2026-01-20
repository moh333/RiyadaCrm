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
        if (Schema::hasTable('vtiger_org_share_action2tab')) {
            return;
        }
        Schema::table('vtiger_org_share_action2tab', function (Blueprint $table) {
            $table->foreign(['tabid'], 'fk_2_vtiger_org_share_action2tab')->references(['tabid'])->on('vtiger_tab')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_org_share_action2tab', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_org_share_action2tab');
        });
    }
};
