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
        Schema::create('vtiger_service', function (Blueprint $table) {
            $table->integer('serviceid')->primary();
            $table->string('service_no', 100);
            $table->string('servicename')->nullable();
            $table->string('servicecategory', 200)->nullable();
            $table->decimal('qty_per_unit', 11)->nullable()->default(0);
            $table->decimal('unit_price', 25, 8)->nullable();
            $table->date('sales_start_date')->nullable();
            $table->date('sales_end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('discontinued')->default(0);
            $table->string('service_usageunit', 200)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('taxclass', 200)->nullable();
            $table->integer('currency_id')->default(1);
            $table->decimal('commissionrate', 7, 3)->nullable();
            $table->string('tags', 1)->nullable();
            $table->decimal('purchase_cost', 27, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_service');
    }
};
