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
        Schema::create('vtiger_currencies', function (Blueprint $table) {
            $table->integer('currencyid', true);
            $table->string('currency_name', 200)->nullable();
            $table->string('currency_code', 50)->nullable();
            $table->string('currency_symbol', 11)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_currencies');
    }
};
