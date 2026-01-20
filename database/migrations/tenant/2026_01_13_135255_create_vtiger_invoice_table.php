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
        if (Schema::hasTable('vtiger_invoice')) {
            return;
        }
        Schema::create('vtiger_invoice', function (Blueprint $table) {
            $table->integer('invoiceid')->default(0)->index('invoice_purchaseorderid_idx');
            $table->string('subject', 100)->nullable();
            $table->integer('salesorderid')->nullable()->index('fk_2_vtiger_invoice');
            $table->string('customerno', 100)->nullable();
            $table->integer('contactid')->nullable();
            $table->string('notes', 100)->nullable();
            $table->date('invoicedate')->nullable();
            $table->date('duedate')->nullable();
            $table->string('invoiceterms', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->decimal('adjustment', 25, 8)->nullable();
            $table->decimal('salescommission', 25, 3)->nullable();
            $table->decimal('exciseduty', 25, 3)->nullable();
            $table->decimal('subtotal', 25, 8)->nullable();
            $table->decimal('total', 25, 8)->nullable();
            $table->string('taxtype', 25)->nullable();
            $table->decimal('discount_percent', 25, 3)->nullable();
            $table->decimal('discount_amount', 25, 8)->nullable();
            $table->decimal('s_h_amount', 25, 8)->nullable();
            $table->string('shipping', 100)->nullable();
            $table->integer('accountid')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('purchaseorder', 200)->nullable();
            $table->string('invoicestatus', 200)->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->integer('currency_id')->default(1);
            $table->decimal('conversion_rate', 10, 3)->default(1);
            $table->text('compound_taxes_info')->nullable();
            $table->decimal('pre_tax_total', 25, 8)->nullable();
            $table->decimal('received', 25, 8)->nullable();
            $table->decimal('balance', 25, 8)->nullable();
            $table->decimal('s_h_percent', 25, 8)->nullable();
            $table->string('potential_id', 100)->nullable();
            $table->string('tags', 1)->nullable();
            $table->integer('region_id')->nullable();

            $table->primary(['invoiceid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_invoice');
    }
};
