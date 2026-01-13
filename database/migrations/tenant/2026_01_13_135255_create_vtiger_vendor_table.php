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
        Schema::create('vtiger_vendor', function (Blueprint $table) {
            $table->integer('vendorid')->default(0)->primary();
            $table->string('vendor_no', 100);
            $table->string('vendorname', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('glacct', 200)->nullable();
            $table->string('category', 50)->nullable();
            $table->text('street')->nullable();
            $table->string('city', 30)->nullable();
            $table->string('state', 30)->nullable();
            $table->string('pobox', 30)->nullable();
            $table->string('postalcode', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_vendor');
    }
};
