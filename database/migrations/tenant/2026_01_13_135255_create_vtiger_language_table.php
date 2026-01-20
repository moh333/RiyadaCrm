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
        if (Schema::hasTable('vtiger_language')) {
            return;
        }
        Schema::create('vtiger_language', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50)->nullable();
            $table->string('prefix', 10)->nullable();
            $table->string('label', 30)->nullable();
            $table->dateTime('lastupdated')->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('isdefault')->nullable();
            $table->integer('active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_language');
    }
};
