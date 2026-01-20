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
        if (Schema::hasTable('vtiger_mailscanner_rules')) {
            return;
        }
        Schema::create('vtiger_mailscanner_rules', function (Blueprint $table) {
            $table->integer('ruleid', true);
            $table->integer('scannerid')->nullable();
            $table->string('fromaddress')->nullable();
            $table->string('toaddress')->nullable();
            $table->string('subjectop', 20)->nullable();
            $table->string('subject')->nullable();
            $table->string('bodyop', 20)->nullable();
            $table->string('body')->nullable();
            $table->string('matchusing', 5)->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailscanner_rules');
    }
};
