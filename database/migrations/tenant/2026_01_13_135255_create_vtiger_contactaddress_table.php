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
        Schema::create('vtiger_contactaddress', function (Blueprint $table) {
            $table->integer('contactaddressid')->default(0)->primary();
            $table->string('mailingcity', 40)->nullable();
            $table->string('mailingstreet', 250)->nullable();
            $table->string('mailingcountry', 40)->nullable();
            $table->string('othercountry', 30)->nullable();
            $table->string('mailingstate', 30)->nullable();
            $table->string('mailingpobox', 30)->nullable();
            $table->string('othercity', 40)->nullable();
            $table->string('otherstate', 50)->nullable();
            $table->string('mailingzip', 30)->nullable();
            $table->string('otherzip', 30)->nullable();
            $table->string('otherstreet', 250)->nullable();
            $table->string('otherpobox', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_contactaddress');
    }
};
