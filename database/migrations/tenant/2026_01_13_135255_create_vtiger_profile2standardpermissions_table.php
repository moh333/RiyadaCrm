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
        Schema::create('vtiger_profile2standardpermissions', function (Blueprint $table) {
            $table->integer('profileid');
            $table->integer('tabid');
            $table->integer('operation');
            $table->integer('permissions')->nullable();

            $table->primary(['profileid', 'tabid', 'operation']);
            $table->index(['profileid', 'tabid', 'operation'], 'profile2standardpermissions_profileid_tabid_operation_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile2standardpermissions');
    }
};
