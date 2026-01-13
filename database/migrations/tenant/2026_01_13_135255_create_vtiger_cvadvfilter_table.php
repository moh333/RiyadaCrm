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
        Schema::create('vtiger_cvadvfilter', function (Blueprint $table) {
            $table->integer('cvid')->index('cvadvfilter_cvid_idx');
            $table->integer('columnindex');
            $table->string('columnname', 250)->nullable()->default('');
            $table->string('comparator', 20)->nullable();
            $table->string('value', 512)->nullable();
            $table->integer('groupid')->nullable()->default(1);
            $table->string('column_condition')->nullable()->default('and');

            $table->primary(['cvid', 'columnindex']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cvadvfilter');
    }
};
