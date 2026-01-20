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
        if (Schema::hasTable('vtiger_duration_minutes')) {
            return;
        }
        Schema::create('vtiger_duration_minutes', function (Blueprint $table) {
            $table->integer('minutesid', true);
            $table->string('duration_minutes', 200);
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
        Schema::dropIfExists('vtiger_duration_minutes');
    }
};
