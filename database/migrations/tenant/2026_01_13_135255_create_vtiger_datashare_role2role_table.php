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
        Schema::create('vtiger_datashare_role2role', function (Blueprint $table) {
            $table->integer('shareid')->primary();
            $table->string('share_roleid')->nullable()->index('datashare_role2role_share_roleid_idx');
            $table->string('to_roleid')->nullable()->index('datashare_role2role_to_roleid_idx');
            $table->integer('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_role2role');
    }
};
