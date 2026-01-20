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
        if (Schema::hasTable('vtiger_asteriskincomingcalls')) {
            return;
        }
        Schema::create('vtiger_asteriskincomingcalls', function (Blueprint $table) {
            $table->string('from_number', 50)->nullable();
            $table->string('from_name', 50)->nullable();
            $table->string('to_number', 50)->nullable();
            $table->string('callertype', 30)->nullable();
            $table->integer('flag')->nullable();
            $table->integer('timer')->nullable();
            $table->string('refuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_asteriskincomingcalls');
    }
};
