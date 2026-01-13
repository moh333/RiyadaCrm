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
        Schema::create('vtiger_calendar_default_activitytypes', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('module', 50)->nullable();
            $table->string('fieldname', 50)->nullable();
            $table->string('defaultcolor', 50)->nullable();
            $table->integer('isdefault')->nullable()->default(1);
            $table->string('conditions')->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_calendar_default_activitytypes');
    }
};
