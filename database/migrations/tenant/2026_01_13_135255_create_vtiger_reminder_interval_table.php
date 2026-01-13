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
        Schema::create('vtiger_reminder_interval', function (Blueprint $table) {
            $table->integer('reminder_intervalid', true);
            $table->string('reminder_interval', 200);
            $table->integer('sortorderid');
            $table->integer('presence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reminder_interval');
    }
};
