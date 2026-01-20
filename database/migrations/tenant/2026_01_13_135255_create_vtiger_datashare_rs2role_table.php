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
        if (Schema::hasTable('vtiger_datashare_rs2role')) {
            return;
        }
        Schema::create('vtiger_datashare_rs2role', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->string('share_roleandsubid')->nullable()->index('datashare_rs2role_share_roleandsubid_idx');
            $table->string('to_roleid')->nullable()->index('datashare_rs2role_to_roleid_idx');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_rs2role');
    }
};
