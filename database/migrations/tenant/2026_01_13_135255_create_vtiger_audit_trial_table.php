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
        Schema::create('vtiger_audit_trial', function (Blueprint $table) {
            $table->integer('auditid')->primary();
            $table->integer('userid')->nullable();
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->string('recordid', 20)->nullable();
            $table->dateTime('actiondate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_audit_trial');
    }
};
