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
        if (Schema::hasTable('com_vtiger_workflowtasks')) {
            return;
        }
        Schema::create('com_vtiger_workflowtasks', function (Blueprint $table) {
            $table->integer('task_id', true)->unique('com_vtiger_workflowtasks_idx');
            $table->integer('workflow_id')->nullable();
            $table->string('summary', 400);
            $table->text('task');

            $table->primary(['task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflowtasks');
    }
};
