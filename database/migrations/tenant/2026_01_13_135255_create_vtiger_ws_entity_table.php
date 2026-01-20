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
        if (Schema::hasTable('vtiger_ws_entity')) {
            return;
        }
        Schema::create('vtiger_ws_entity', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 25);
            $table->string('handler_path');
            $table->string('handler_class', 64);
            $table->integer('ismodule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_entity');
    }
};
