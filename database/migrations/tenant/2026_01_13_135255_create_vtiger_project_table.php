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
        Schema::create('vtiger_project', function (Blueprint $table) {
            $table->integer('projectid')->primary();
            $table->string('projectname')->nullable();
            $table->string('project_no', 100)->nullable();
            $table->date('startdate')->nullable();
            $table->date('targetenddate')->nullable();
            $table->date('actualenddate')->nullable();
            $table->string('targetbudget')->nullable();
            $table->string('projecturl')->nullable();
            $table->string('projectstatus', 100)->nullable();
            $table->string('projectpriority', 100)->nullable();
            $table->string('projecttype', 100)->nullable();
            $table->string('progress', 100)->nullable();
            $table->string('linktoaccountscontacts', 100)->nullable();
            $table->string('tags', 1)->nullable();
            $table->integer('isconvertedfrompotential')->default(0);
            $table->string('potentialid', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_project');
    }
};
