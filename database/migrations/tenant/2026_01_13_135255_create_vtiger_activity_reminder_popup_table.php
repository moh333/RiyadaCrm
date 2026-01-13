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
        Schema::create('vtiger_activity_reminder_popup', function (Blueprint $table) {
            $table->integer('reminderid', true);
            $table->string('semodule', 100);
            $table->integer('recordid');
            $table->date('date_start');
            $table->string('time_start', 100);
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activity_reminder_popup');
    }
};
