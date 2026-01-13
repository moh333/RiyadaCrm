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
        Schema::create('vtiger_products', function (Blueprint $table) {
            $table->integer('productid')->primary();
            $table->string('product_no', 100);
            $table->string('productname')->nullable();
            $table->string('productcode', 40)->nullable();
            $table->string('productcategory', 200)->nullable();
            $table->string('manufacturer', 200)->nullable();
            $table->decimal('qty_per_unit', 11)->nullable()->default(0);
            $table->decimal('unit_price', 25, 8)->nullable();
            $table->decimal('weight', 11, 3)->nullable();
            $table->integer('pack_size')->nullable();
            $table->date('sales_start_date')->nullable();
            $table->date('sales_end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('cost_factor')->nullable();
            $table->decimal('commissionrate', 7, 3)->nullable();
            $table->string('commissionmethod', 50)->nullable();
            $table->integer('discontinued')->default(0);
            $table->string('usageunit', 200)->nullable();
            $table->integer('reorderlevel')->nullable();
            $table->string('website', 100)->nullable();
            $table->string('taxclass', 200)->nullable();
            $table->string('mfr_part_no', 200)->nullable();
            $table->string('vendor_part_no', 200)->nullable();
            $table->string('serialno', 200)->nullable();
            $table->decimal('qtyinstock', 25, 3)->nullable();
            $table->string('productsheet', 200)->nullable();
            $table->integer('qtyindemand')->nullable();
            $table->string('glacct', 200)->nullable();
            $table->integer('vendor_id')->nullable();
            $table->text('imagename')->nullable();
            $table->integer('currency_id')->default(1);
            $table->integer('is_subproducts_viewable')->nullable()->default(1);
            $table->decimal('purchase_cost', 27, 8)->nullable();
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_products');
    }
};
