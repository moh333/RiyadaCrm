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
        if (Schema::hasTable('vtiger_inventorysubproductrel')) {
            return;
        }
        Schema::create('vtiger_inventorysubproductrel', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('sequence_no');
            $table->integer('productid');
            $table->integer('quantity')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventorysubproductrel');
    }
};
