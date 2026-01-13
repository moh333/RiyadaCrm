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
        Schema::create('com_vtiger_workflows', function (Blueprint $table) {
            $table->integer('workflow_id', true)->unique('com_vtiger_workflows_idx');
            $table->string('module_name', 100)->nullable();
            $table->string('summary', 400);
            $table->text('test');
            $table->integer('execution_condition');
            $table->integer('defaultworkflow')->nullable();
            $table->string('type')->nullable();
            $table->integer('filtersavedinnew')->nullable();
            $table->integer('schtypeid')->nullable();
            $table->string('schdayofmonth', 100)->nullable();
            $table->string('schdayofweek', 100)->nullable();
            $table->string('schannualdates', 500)->nullable();
            $table->string('schtime', 50)->nullable();
            $table->dateTime('nexttrigger_time')->nullable();
            $table->boolean('status')->nullable()->default(true);
            $table->string('workflowname', 100)->nullable();

            $table->primary(['workflow_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflows');
    }
};
