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
        if (Schema::hasTable('vtiger_currency')) {
            return;
        }
        Schema::create('vtiger_currency', function (Blueprint $table) {
            $table->integer('currencyid', true);
            $table->string('currency', 200)->unique('currency_currency_idx');
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_currency');
    }
};
