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
        if (Schema::hasTable('vtiger_schedulereports')) {
            return;
        }
        Schema::create('vtiger_schedulereports', function (Blueprint $table) {
            $table->integer('reportid')->nullable();
            $table->integer('scheduleid')->nullable();
            $table->text('recipients')->nullable();
            $table->string('schdate', 20)->nullable();
            $table->time('schtime')->nullable();
            $table->string('schdayoftheweek', 100)->nullable();
            $table->string('schdayofthemonth', 100)->nullable();
            $table->string('schannualdates', 500)->nullable();
            $table->string('specificemails', 500)->nullable();
            $table->timestamp('next_trigger_time')->nullable()->useCurrent();
            $table->string('fileformat', 10)->nullable()->default('CSV');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_schedulereports');
    }
};
