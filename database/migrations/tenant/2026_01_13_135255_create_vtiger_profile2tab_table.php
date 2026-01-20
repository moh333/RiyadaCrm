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
        if (Schema::hasTable('vtiger_profile2tab')) {
            return;
        }
        Schema::create('vtiger_profile2tab', function (Blueprint $table) {
            $table->integer('profileid')->nullable();
            $table->integer('tabid')->nullable();
            $table->integer('permissions')->default(0);

            $table->index(['profileid', 'tabid'], 'profile2tab_profileid_tabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile2tab');
    }
};
