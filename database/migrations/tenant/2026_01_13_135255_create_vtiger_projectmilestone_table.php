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
        if (Schema::hasTable('vtiger_projectmilestone')) {
            return;
        }
        Schema::create('vtiger_projectmilestone', function (Blueprint $table) {
            $table->integer('projectmilestoneid')->primary();
            $table->string('projectmilestonename')->nullable();
            $table->string('projectmilestone_no', 100)->nullable();
            $table->string('projectmilestonedate')->nullable();
            $table->string('projectid', 100)->nullable();
            $table->string('projectmilestonetype', 100)->nullable();
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_projectmilestone');
    }
};
