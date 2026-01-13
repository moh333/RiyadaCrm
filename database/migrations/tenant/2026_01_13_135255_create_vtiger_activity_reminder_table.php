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
        Schema::create('vtiger_activity_reminder', function (Blueprint $table) {
            $table->integer('activity_id');
            $table->integer('reminder_time');
            $table->integer('reminder_sent');
            $table->integer('recurringid');

            $table->primary(['activity_id', 'recurringid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activity_reminder');
    }
};
