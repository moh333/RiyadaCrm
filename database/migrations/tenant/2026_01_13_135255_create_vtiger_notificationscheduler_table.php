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
        Schema::create('vtiger_notificationscheduler', function (Blueprint $table) {
            $table->integer('schedulednotificationid', true);
            $table->string('schedulednotificationname', 200)->nullable()->unique('notificationscheduler_schedulednotificationname_idx');
            $table->integer('active')->nullable();
            $table->string('notificationsubject', 200)->nullable();
            $table->text('notificationbody')->nullable();
            $table->string('label', 50)->nullable();
            $table->string('type', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_notificationscheduler');
    }
};
