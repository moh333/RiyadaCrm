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
        Schema::create('vtiger_quotes', function (Blueprint $table) {
            $table->integer('quoteid')->default(0)->primary();
            $table->string('subject', 100)->nullable();
            $table->integer('potentialid')->nullable()->index('quotes_potentialid_idx');
            $table->string('quotestage', 200)->nullable()->index('quote_quotestage_idx');
            $table->date('validtill')->nullable();
            $table->integer('contactid')->nullable()->index('quotes_contactid_idx');
            $table->string('quote_no', 100)->nullable();
            $table->decimal('subtotal', 25, 8)->nullable();
            $table->string('carrier', 200)->nullable();
            $table->string('shipping', 100)->nullable();
            $table->integer('inventorymanager')->nullable();
            $table->string('type', 100)->nullable();
            $table->decimal('adjustment', 25, 8)->nullable();
            $table->decimal('total', 25, 8)->nullable();
            $table->string('taxtype', 25)->nullable();
            $table->decimal('discount_percent', 25, 3)->nullable();
            $table->decimal('discount_amount', 25, 8)->nullable();
            $table->decimal('s_h_amount', 25, 8)->nullable();
            $table->integer('accountid')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->integer('currency_id')->default(1);
            $table->decimal('conversion_rate', 10, 3)->default(1);
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
        Schema::dropIfExists('vtiger_quotes');
    }
};
