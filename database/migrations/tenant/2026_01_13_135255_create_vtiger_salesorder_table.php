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
        if (Schema::hasTable('vtiger_salesorder')) {
            return;
        }
        Schema::create('vtiger_salesorder', function (Blueprint $table) {
            $table->integer('salesorderid')->default(0)->primary();
            $table->string('subject', 100)->nullable();
            $table->integer('potentialid')->nullable();
            $table->string('customerno', 100)->nullable();
            $table->string('salesorder_no', 100)->nullable();
            $table->integer('quoteid')->nullable();
            $table->string('vendorterms', 100)->nullable();
            $table->integer('contactid')->nullable()->index('salesorder_contactid_idx');
            $table->integer('vendorid')->nullable()->index('salesorder_vendorid_idx');
            $table->date('duedate')->nullable();
            $table->string('carrier', 200)->nullable();
            $table->string('pending', 200)->nullable();
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
            $table->integer('accountid')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('purchaseorder', 200)->nullable();
            $table->string('sostatus', 200)->nullable();
            $table->integer('currency_id')->default(1);
            $table->decimal('conversion_rate', 10, 3)->default(1);
            $table->integer('enable_recurring')->nullable()->default(0);
            $table->text('compound_taxes_info')->nullable();
            $table->decimal('pre_tax_total', 25, 8)->nullable();
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
        Schema::dropIfExists('vtiger_salesorder');
    }
};
