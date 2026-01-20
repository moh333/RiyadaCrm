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
        Schema::create('vtiger_org_share_action2tab', function (Blueprint $table) {
            $table->integer('share_action_id');
            $table->integer('tabid')->index('fk_2_vtiger_org_share_action2tab');

            $table->primary(['share_action_id', 'tabid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_org_share_action2tab');
    }
};
