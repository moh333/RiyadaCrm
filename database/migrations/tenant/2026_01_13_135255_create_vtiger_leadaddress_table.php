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
        if (Schema::hasTable('vtiger_leadaddress')) {
            return;
        }
        Schema::create('vtiger_leadaddress', function (Blueprint $table) {
            $table->integer('leadaddressid')->default(0)->primary();
            $table->string('city', 30)->nullable();
            $table->string('code', 30)->nullable();
            $table->string('state', 30)->nullable();
            $table->string('pobox', 30)->nullable();
            $table->string('country', 30)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('lane', 250)->nullable();
            $table->string('leadaddresstype', 30)->nullable()->default('Billing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_leadaddress');
    }
};
