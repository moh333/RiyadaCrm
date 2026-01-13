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
        Schema::create('vtiger_pricebook', function (Blueprint $table) {
            $table->integer('pricebookid')->default(0)->primary();
            $table->string('pricebook_no', 100);
            $table->string('bookname', 100)->nullable();
            $table->integer('active')->nullable();
            $table->integer('currency_id')->default(1);
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pricebook');
    }
};
