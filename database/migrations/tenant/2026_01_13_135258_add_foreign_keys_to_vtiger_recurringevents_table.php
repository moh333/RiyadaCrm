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
        if (Schema::hasTable('vtiger_recurringevents')) {
            return;
        }
        Schema::table('vtiger_recurringevents', function (Blueprint $table) {
            $table->foreign(['activityid'], 'fk_1_vtiger_recurringevents')->references(['activityid'])->on('vtiger_activity')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_recurringevents', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_recurringevents');
        });
    }
};
