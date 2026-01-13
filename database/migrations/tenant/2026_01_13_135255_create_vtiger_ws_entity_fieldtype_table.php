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
        Schema::create('vtiger_ws_entity_fieldtype', function (Blueprint $table) {
            $table->integer('fieldtypeid', true);
            $table->string('table_name', 50);
            $table->string('field_name', 50);
            $table->string('fieldtype', 200);

            $table->unique(['table_name', 'field_name'], 'vtiger_idx_1_tablename_fieldname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_entity_fieldtype');
    }
};
