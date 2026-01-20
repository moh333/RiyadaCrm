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
        if (Schema::hasTable('vtiger_ws_fieldtype')) {
            return;
        }
        Schema::create('vtiger_ws_fieldtype', function (Blueprint $table) {
            $table->integer('fieldtypeid', true);
            $table->string('uitype', 30)->unique('uitype_idx');
            $table->string('fieldtype', 200);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_fieldtype');
    }
};
