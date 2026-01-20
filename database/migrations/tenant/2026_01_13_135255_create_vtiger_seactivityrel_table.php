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
        if (Schema::hasTable('vtiger_seactivityrel')) {
            return;
        }
        Schema::create('vtiger_seactivityrel', function (Blueprint $table) {
            $table->integer('crmid')->index('seactivityrel_crmid_idx');
            $table->integer('activityid')->index('seactivityrel_activityid_idx');

            $table->primary(['crmid', 'activityid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_seactivityrel');
    }
};
