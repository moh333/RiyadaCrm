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
        Schema::create('vtiger_contactsubdetails', function (Blueprint $table) {
            $table->integer('contactsubscriptionid')->default(0)->primary();
            $table->string('homephone', 50)->nullable();
            $table->string('otherphone', 50)->nullable();
            $table->string('assistant', 30)->nullable();
            $table->string('assistantphone', 50)->nullable();
            $table->date('birthday')->nullable();
            $table->integer('laststayintouchrequest')->nullable()->default(0);
            $table->integer('laststayintouchsavedate')->nullable()->default(0);
            $table->string('leadsource', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_contactsubdetails');
    }
};
