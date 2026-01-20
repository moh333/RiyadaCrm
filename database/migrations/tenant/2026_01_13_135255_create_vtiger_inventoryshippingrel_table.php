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
        if (Schema::hasTable('vtiger_inventoryshippingrel')) {
            return;
        }
        Schema::create('vtiger_inventoryshippingrel', function (Blueprint $table) {
            $table->integer('id')->nullable()->index('inventoryishippingrel_id_idx');
            $table->decimal('shtax1', 7, 3)->nullable();
            $table->decimal('shtax2', 7, 3)->nullable();
            $table->decimal('shtax3', 7, 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventoryshippingrel');
    }
};
