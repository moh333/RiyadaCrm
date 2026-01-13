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
        Schema::create('vtiger_activity', function (Blueprint $table) {
            $table->integer('activityid')->default(0)->primary();
            $table->string('subject')->nullable();
            $table->string('semodule', 20)->nullable();
            $table->string('activitytype', 200);
            $table->date('date_start');
            $table->date('due_date')->nullable();
            $table->string('time_start', 50)->nullable();
            $table->string('time_end', 50)->nullable();
            $table->string('sendnotification', 3)->default('0');
            $table->string('duration_hours', 200)->nullable();
            $table->string('duration_minutes', 200)->nullable();
            $table->string('status', 200)->nullable()->index('activity_status_idx');
            $table->string('eventstatus', 200)->nullable()->index('activity_eventstatus_idx');
            $table->string('priority', 200)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('notime', 3)->default('0');
            $table->string('visibility', 50)->default('all');
            $table->string('recurringtype', 200)->nullable();
            $table->string('tags', 1)->nullable();

            $table->index(['activityid', 'subject'], 'activity_activityid_subject_idx');
            $table->index(['activitytype', 'date_start'], 'activity_activitytype_date_start_idx');
            $table->index(['date_start', 'due_date'], 'activity_date_start_due_date_idx');
            $table->index(['date_start', 'time_start'], 'activity_date_start_time_start_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activity');
    }
};
