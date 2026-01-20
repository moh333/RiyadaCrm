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
        if (Schema::hasTable('vtiger_ws_fieldinfo')) {
            return;
        }
        Schema::create('vtiger_ws_fieldinfo', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('property_name', 32)->nullable();
            $table->string('property_value', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_fieldinfo');
    }
};
