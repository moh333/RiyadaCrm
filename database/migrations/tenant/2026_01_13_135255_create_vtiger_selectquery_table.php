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
        Schema::create('vtiger_selectquery', function (Blueprint $table) {
            $table->integer('queryid')->primary();
            $table->integer('startindex')->nullable()->default(0);
            $table->integer('numofobjects')->nullable()->default(0);

            $table->index(['queryid'], 'selectquery_queryid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_selectquery');
    }
};
