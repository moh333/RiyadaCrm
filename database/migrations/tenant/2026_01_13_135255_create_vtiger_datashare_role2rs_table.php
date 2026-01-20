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
        if (Schema::hasTable('vtiger_datashare_role2rs')) {
            return;
        }
        Schema::create('vtiger_datashare_role2rs', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->string('share_roleid')->nullable()->index('datashare_role2s_share_roleid_idx');
            $table->string('to_roleandsubid')->nullable()->index('datashare_role2s_to_roleandsubid_idx');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_role2rs');
    }
};
