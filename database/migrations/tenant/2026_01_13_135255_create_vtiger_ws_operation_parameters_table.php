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
        Schema::create('vtiger_ws_operation_parameters', function (Blueprint $table) {
            $table->integer('operationid', true);
            $table->string('name', 128);
            $table->string('type', 64);
            $table->integer('sequence');

            $table->primary(['operationid', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_operation_parameters');
    }
};
