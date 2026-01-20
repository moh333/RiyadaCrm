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
        if (Schema::hasTable('com_vtiger_workflowtask_queue')) {
            return;
        }
        Schema::create('com_vtiger_workflowtask_queue', function (Blueprint $table) {
            $table->integer('task_id')->nullable();
            $table->string('entity_id', 100)->nullable();
            $table->integer('do_after')->nullable();
            $table->string('relatedinfo')->nullable();
            $table->text('task_contents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflowtask_queue');
    }
};
