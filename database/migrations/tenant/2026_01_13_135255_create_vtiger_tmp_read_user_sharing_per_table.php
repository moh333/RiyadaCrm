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
        Schema::create('vtiger_tmp_read_user_sharing_per', function (Blueprint $table) {
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('shareduserid');

            $table->primary(['userid', 'tabid', 'shareduserid']);
            $table->index(['userid', 'shareduserid'], 'tmp_read_user_sharing_per_userid_shareduserid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tmp_read_user_sharing_per');
    }
};
