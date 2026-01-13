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
        Schema::create('vtiger_mailer_queue', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('fromname', 100)->nullable();
            $table->string('fromemail', 100)->nullable();
            $table->string('mailer', 10)->nullable();
            $table->string('content_type', 15)->nullable();
            $table->string('subject', 999)->nullable();
            $table->text('body')->nullable();
            $table->integer('relcrmid')->nullable();
            $table->integer('failed')->default(0);
            $table->string('failreason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailer_queue');
    }
};
