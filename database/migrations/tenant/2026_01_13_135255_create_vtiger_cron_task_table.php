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
        if (Schema::hasTable('vtiger_cron_task')) {
            return;
        }
        Schema::create('vtiger_cron_task', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 100)->nullable()->unique('name');
            $table->string('handler_file', 100)->nullable()->unique('handler_file');
            $table->integer('frequency')->nullable();
            $table->unsignedInteger('laststart')->nullable();
            $table->unsignedInteger('lastend')->nullable();
            $table->integer('status')->nullable();
            $table->string('module', 100)->nullable();
            $table->integer('sequence')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cron_task');
    }
};
