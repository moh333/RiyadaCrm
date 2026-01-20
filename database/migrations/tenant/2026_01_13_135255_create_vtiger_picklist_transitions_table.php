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
        if (Schema::hasTable('vtiger_picklist_transitions')) {
            return;
        }
        Schema::create('vtiger_picklist_transitions', function (Blueprint $table) {
            $table->string('fieldname')->primary();
            $table->string('module', 100);
            $table->string('transition_data', 1000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_picklist_transitions');
    }
};
