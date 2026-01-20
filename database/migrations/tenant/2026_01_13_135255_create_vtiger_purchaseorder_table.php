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
        if (Schema::hasTable('vtiger_purchaseorder')) {
            return;
        }
        Schema::create('vtiger_purchaseorder', function (Blueprint $table) {
            $table->integer('purchaseorderid')->default(0)->primary();
            $table->string('subject', 100)->nullable();
            $table->integer('quoteid')->nullable()->index('purchaseorder_quoteid_idx');
            $table->integer('vendorid')->nullable()->index('purchaseorder_vendorid_idx');
            $table->string('requisition_no', 100)->nullable();
            $table->string('purchaseorder_no', 100)->nullable();
            $table->string('tracking_no', 100)->nullable();
            $table->integer('contactid')->nullable()->index('purchaseorder_contactid_idx');
            $table->date('duedate')->nullable();
            $table->string('carrier', 200)->nullable();
            $table->string('type', 100)->nullable();
            $table->decimal('adjustment', 25, 8)->nullable();
            $table->decimal('salescommission', 25, 3)->nullable();
            $table->decimal('exciseduty', 25, 3)->nullable();
            $table->decimal('total', 25, 8)->nullable();
            $table->decimal('subtotal', 25, 8)->nullable();
            $table->string('taxtype', 25)->nullable();
            $table->decimal('discount_percent', 25, 3)->nullable();
            $table->decimal('discount_amount', 25, 8)->nullable();
            $table->decimal('s_h_amount', 25, 8)->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('postatus', 200)->nullable();
            $table->integer('currency_id')->default(1);
            $table->decimal('conversion_rate', 10, 3)->default(1);
            $table->text('compound_taxes_info')->nullable();
            $table->decimal('pre_tax_total', 25, 8)->nullable();
            $table->decimal('paid', 25, 8)->nullable();
            $table->decimal('balance', 25, 8)->nullable();
            $table->decimal('s_h_percent', 25, 3)->nullable();
            $table->string('tags', 1)->nullable();
            $table->integer('region_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_purchaseorder');
    }
};
