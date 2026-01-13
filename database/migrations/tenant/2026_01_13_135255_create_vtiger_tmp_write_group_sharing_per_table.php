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
        Schema::create('vtiger_tmp_write_group_sharing_per', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('sharedgroupid');

            $table->primary(['userid', 'tabid', 'sharedgroupid']);
            $table->index(['userid', 'sharedgroupid'], 'tmp_write_group_sharing_per_uk1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tmp_write_group_sharing_per');
    }
};
