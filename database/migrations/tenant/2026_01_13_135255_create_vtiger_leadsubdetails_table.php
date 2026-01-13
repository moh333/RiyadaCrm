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
        Schema::create('vtiger_leadsubdetails', function (Blueprint $table) {
            $table->integer('leadsubscriptionid')->default(0)->primary();
            $table->string('website')->nullable();
            $table->integer('callornot')->nullable()->default(0);
            $table->integer('readornot')->nullable()->default(0);
            $table->integer('empct')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_leadsubdetails');
    }
};
