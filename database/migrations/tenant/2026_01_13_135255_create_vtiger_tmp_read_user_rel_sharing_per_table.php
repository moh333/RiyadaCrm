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
        if (Schema::hasTable('vtiger_tmp_read_user_rel_sharing_per')) {
            return;
        }
        Schema::create('vtiger_tmp_read_user_rel_sharing_per', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('relatedtabid');
            $table->integer('shareduserid');

            $table->primary(['userid', 'tabid', 'relatedtabid', 'shareduserid']);
            $table->index(['userid', 'shareduserid', 'relatedtabid'], 'tmp_read_user_rel_sharing_per_userid_shared_reltabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tmp_read_user_rel_sharing_per');
    }
};
