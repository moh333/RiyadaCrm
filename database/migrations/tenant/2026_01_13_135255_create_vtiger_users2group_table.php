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
        if (Schema::hasTable('vtiger_users2group')) {
            return;
        }
        Schema::create('vtiger_users2group', function (Blueprint $table) {
            $table->integer('groupid');
            $table->integer('userid')->index('fk_2_vtiger_users2group');

            $table->primary(['groupid', 'userid']);
            $table->index(['groupid', 'userid'], 'users2group_groupname_uerid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_users2group');
    }
};
