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
        if (Schema::hasTable('vtiger_datashare_role2group')) {
            return;
        }
        Schema::create('vtiger_datashare_role2group', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->string('share_roleid')->nullable()->index('idx_datashare_role2group_share_roleid');
            $table->integer('to_groupid')->nullable()->index('idx_datashare_role2group_to_groupid');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_role2group');
    }
};
