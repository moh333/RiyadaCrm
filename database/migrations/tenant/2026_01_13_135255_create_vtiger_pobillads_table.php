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
        if (Schema::hasTable('vtiger_pobillads')) {
            return;
        }
        Schema::create('vtiger_pobillads', function (Blueprint $table) {
            $table->integer('pobilladdressid')->default(0)->primary();
            $table->string('bill_city', 30)->nullable();
            $table->string('bill_code', 30)->nullable();
            $table->string('bill_country', 30)->nullable();
            $table->string('bill_state', 30)->nullable();
            $table->string('bill_street', 250)->nullable();
            $table->string('bill_pobox', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pobillads');
    }
};
