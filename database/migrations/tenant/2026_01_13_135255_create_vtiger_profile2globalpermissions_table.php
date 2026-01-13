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
        Schema::create('vtiger_profile2globalpermissions', function (Blueprint $table) {
            $table->integer('profileid');
            $table->integer('globalactionid');
            $table->integer('globalactionpermission')->nullable();

            $table->index(['profileid', 'globalactionid'], 'idx_profile2globalpermissions');
            $table->primary(['profileid', 'globalactionid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile2globalpermissions');
    }
};
