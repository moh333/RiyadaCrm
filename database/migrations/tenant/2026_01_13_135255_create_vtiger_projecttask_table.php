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
        Schema::create('vtiger_projecttask', function (Blueprint $table) {
            $table->integer('projecttaskid')->primary();
            $table->string('projecttaskname')->nullable();
            $table->string('projecttask_no', 100)->nullable();
            $table->string('projecttasktype', 100)->nullable();
            $table->string('projecttaskpriority', 100)->nullable();
            $table->string('projecttaskprogress', 100)->nullable();
            $table->string('projecttaskhours')->nullable();
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
            $table->string('projectid', 100)->nullable();
            $table->integer('projecttasknumber')->nullable();
            $table->string('tags', 1)->nullable();
            $table->string('projecttaskstatus', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_projecttask');
    }
};
