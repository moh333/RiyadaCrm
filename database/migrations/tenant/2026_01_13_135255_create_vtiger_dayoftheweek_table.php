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
        if (Schema::hasTable('vtiger_dayoftheweek')) {
            return;
        }
        Schema::create('vtiger_dayoftheweek', function (Blueprint $table) {
            $table->integer('dayoftheweekid', true);
            $table->string('dayoftheweek', 200);
            $table->integer('sortorderid')->nullable();
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_dayoftheweek');
    }
};
