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
        Schema::create('vtiger_mailer_queueattachments', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->text('path')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('encoding', 50)->nullable();
            $table->string('type', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailer_queueattachments');
    }
};
