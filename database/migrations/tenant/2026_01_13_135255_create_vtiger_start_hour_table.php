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
        if (Schema::hasTable('vtiger_start_hour')) {
            return;
        }
        Schema::create('vtiger_start_hour', function (Blueprint $table) {
            $table->integer('start_hourid', true);
            $table->string('start_hour', 200);
            $table->integer('sortorderid')->nullable();
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_start_hour');
    }
};
