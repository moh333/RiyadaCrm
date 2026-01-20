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
        if (Schema::hasTable('vtiger_datashare_grp2grp')) {
            return;
        }
        Schema::create('vtiger_datashare_grp2grp', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->integer('share_groupid')->nullable()->index('datashare_grp2grp_share_groupid_idx');
            $table->integer('to_groupid')->nullable()->index('datashare_grp2grp_to_groupid_idx');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_grp2grp');
    }
};
