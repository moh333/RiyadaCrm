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
        if (Schema::hasTable('vtiger_datashare_grp2role')) {
            return;
        }
        Schema::create('vtiger_datashare_grp2role', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->integer('share_groupid')->nullable()->index('idx_datashare_grp2role_share_groupid');
            $table->string('to_roleid')->nullable()->index('idx_datashare_grp2role_to_roleid');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_grp2role');
    }
};
