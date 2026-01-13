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
        Schema::create('vtiger_ws_entity_name', function (Blueprint $table) {
            $table->integer('entity_id')->primary();
            $table->string('name_fields', 50);
            $table->string('index_field', 50);
            $table->string('table_name', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_entity_name');
    }
};
