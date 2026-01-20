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
        if (Schema::hasTable('vtiger_durationmins')) {
            return;
        }
        Schema::create('vtiger_durationmins', function (Blueprint $table) {
            $table->integer('minsid', true);
            $table->string('mins', 50)->nullable();
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_durationmins');
    }
};
