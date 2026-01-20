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
        if (Schema::hasTable('vtiger_modtracker_basic')) {
            return;
        }
        Schema::create('vtiger_modtracker_basic', function (Blueprint $table) {
            $table->integer('id')->index('idx');
            $table->integer('crmid')->nullable()->index('crmidx');
            $table->string('module', 50)->nullable();
            $table->integer('whodid')->nullable();
            $table->dateTime('changedon')->nullable();
            $table->integer('status')->nullable()->default(0);

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_modtracker_basic');
    }
};
