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
        Schema::create('vtiger_recurring_frequency', function (Blueprint $table) {
            $table->integer('recurring_frequency_id')->nullable();
            $table->string('recurring_frequency', 200)->nullable();
            $table->integer('sortorderid')->nullable();
            $table->integer('presence')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_recurring_frequency');
    }
};
