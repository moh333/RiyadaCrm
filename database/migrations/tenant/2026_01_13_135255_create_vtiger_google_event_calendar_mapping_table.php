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
        if (Schema::hasTable('vtiger_google_event_calendar_mapping')) {
            return;
        }
        Schema::create('vtiger_google_event_calendar_mapping', function (Blueprint $table) {
            $table->string('event_id')->nullable();
            $table->string('calendar_id')->nullable();
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_google_event_calendar_mapping');
    }
};
