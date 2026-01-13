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
        Schema::create('vtiger_currency_info', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('currency_name', 100)->nullable();
            $table->string('currency_code', 100)->nullable();
            $table->string('currency_symbol', 30)->nullable();
            $table->decimal('conversion_rate', 12, 5)->nullable();
            $table->string('currency_status', 25)->nullable();
            $table->string('defaultid', 10)->default('0');
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_currency_info');
    }
};
