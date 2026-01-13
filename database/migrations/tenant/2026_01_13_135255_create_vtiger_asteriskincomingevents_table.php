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
        Schema::create('vtiger_asteriskincomingevents', function (Blueprint $table) {
            $table->string('uid')->primary();
            $table->string('channel', 100)->nullable();
            $table->bigInteger('from_number')->nullable();
            $table->string('from_name', 100)->nullable();
            $table->bigInteger('to_number')->nullable();
            $table->string('callertype', 100)->nullable();
            $table->integer('timer')->nullable();
            $table->string('flag', 3)->nullable();
            $table->integer('pbxrecordid')->nullable();
            $table->integer('relcrmid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_asteriskincomingevents');
    }
};
