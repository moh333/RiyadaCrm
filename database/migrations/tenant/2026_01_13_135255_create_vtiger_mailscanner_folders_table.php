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
        Schema::create('vtiger_mailscanner_folders', function (Blueprint $table) {
            $table->integer('folderid', true)->index('folderid_idx');
            $table->integer('scannerid')->nullable();
            $table->string('foldername')->nullable();
            $table->string('lastscan', 30)->nullable();
            $table->integer('rescan')->nullable();
            $table->integer('enabled')->nullable();

            $table->primary(['folderid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailscanner_folders');
    }
};
