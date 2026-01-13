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
        Schema::create('vtiger_tmp_read_group_rel_sharing_per', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('relatedtabid');
            $table->integer('sharedgroupid');

            $table->primary(['userid', 'tabid', 'relatedtabid', 'sharedgroupid']);
            $table->index(['userid', 'sharedgroupid', 'tabid'], 'tmp_read_group_rel_sharing_per_userid_sharedgroupid_tabid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tmp_read_group_rel_sharing_per');
    }
};
