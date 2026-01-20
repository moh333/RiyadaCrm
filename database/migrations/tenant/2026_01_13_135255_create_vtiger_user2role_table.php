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
        if (Schema::hasTable('vtiger_user2role')) {
            return;
        }
        Schema::create('vtiger_user2role', function (Blueprint $table) {
            $table->integer('userid')->primary();
            $table->string('roleid')->index('user2role_roleid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_user2role');
    }
};
