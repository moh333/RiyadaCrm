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
        if (Schema::hasTable('vtiger_visibility')) {
            return;
        }
        Schema::create('vtiger_visibility', function (Blueprint $table) {
            $table->integer('visibilityid', true);
            $table->string('visibility', 200)->unique('visibility_visibility_idx');
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_visibility');
    }
};
