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
        Schema::create('vtiger_opportunitystage', function (Blueprint $table) {
            $table->integer('potstageid', true);
            $table->string('stage', 200)->unique('opportunitystage_stage_idx');
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
            $table->decimal('probability', 3)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_opportunitystage');
    }
};
