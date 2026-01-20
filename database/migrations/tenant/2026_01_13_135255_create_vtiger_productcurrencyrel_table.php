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
        if (Schema::hasTable('vtiger_productcurrencyrel')) {
            return;
        }
        Schema::create('vtiger_productcurrencyrel', function (Blueprint $table) {
            $table->integer('productid');
            $table->integer('currencyid');
            $table->decimal('converted_price', 28, 8)->nullable();
            $table->decimal('actual_price', 28, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_productcurrencyrel');
    }
};
