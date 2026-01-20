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
        if (Schema::hasTable('vtiger_profile2utility')) {
            return;
        }
        Schema::create('vtiger_profile2utility', function (Blueprint $table) {
            $table->integer('profileid');
            $table->integer('tabid');
            $table->integer('activityid');
            $table->integer('permission')->nullable();

            $table->primary(['profileid', 'tabid', 'activityid']);
            $table->index(['profileid', 'tabid', 'activityid'], 'profile2utility_profileid_tabid_activityid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile2utility');
    }
};
