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
        Schema::create('vtiger_recurringtype', function (Blueprint $table) {
            $table->integer('recurringeventid', true);
            $table->string('recurringtype', 200)->unique('recurringtype_status_idx');
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
        Schema::dropIfExists('vtiger_recurringtype');
    }
};
