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
        Schema::create('vtiger_report', function (Blueprint $table) {
            $table->integer('reportid')->primary();
            $table->integer('folderid')->index('report_folderid_idx');
            $table->string('reportname', 100)->nullable()->default('');
            $table->string('description', 250)->nullable()->default('');
            $table->string('reporttype', 50)->nullable()->default('');
            $table->integer('queryid')->default(0)->index('report_queryid_idx');
            $table->string('state', 50)->nullable()->default('SAVED');
            $table->integer('customizable')->nullable()->default(1);
            $table->integer('category')->nullable()->default(1);
            $table->integer('owner')->nullable()->default(1);
            $table->string('sharingtype', 200)->nullable()->default('Private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_report');
    }
};
