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
        Schema::create('com_vtiger_workflow_tasktypes', function (Blueprint $table) {
            $table->integer('id');
            $table->string('tasktypename');
            $table->string('label')->nullable();
            $table->string('classname')->nullable();
            $table->string('classpath')->nullable();
            $table->string('templatepath')->nullable();
            $table->string('modules', 500)->nullable();
            $table->string('sourcemodule')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflow_tasktypes');
    }
};
