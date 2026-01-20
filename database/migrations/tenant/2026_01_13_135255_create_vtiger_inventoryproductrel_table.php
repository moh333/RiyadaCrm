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
        if (Schema::hasTable('vtiger_inventoryproductrel')) {
            return;
        }
        Schema::create('vtiger_inventoryproductrel', function (Blueprint $table) {
            $table->integer('id')->nullable()->index('inventoryproductrel_id_idx');
            $table->integer('productid')->nullable()->index('inventoryproductrel_productid_idx');
            $table->integer('sequence_no')->nullable();
            $table->decimal('quantity', 25, 3)->nullable();
            $table->decimal('listprice', 27, 8)->nullable();
            $table->decimal('discount_percent', 7, 3)->nullable();
            $table->decimal('discount_amount', 27, 8)->nullable();
            $table->text('comment')->nullable();
            $table->text('description')->nullable();
            $table->integer('incrementondel')->default(0);
            $table->integer('lineitem_id', true);
            $table->decimal('tax1', 7, 3)->nullable();
            $table->decimal('tax2', 7, 3)->nullable();
            $table->decimal('tax3', 7, 3)->nullable();
            $table->string('image', 2)->nullable();
            $table->decimal('purchase_cost', 27, 8)->nullable();
            $table->decimal('margin', 27, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventoryproductrel');
    }
};
