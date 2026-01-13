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
        Schema::create('vtiger_ws_operation', function (Blueprint $table) {
            $table->integer('operationid', true);
            $table->string('name', 128);
            $table->string('handler_path');
            $table->string('handler_method', 64);
            $table->string('type', 8);
            $table->integer('prelogin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_operation');
    }
};
