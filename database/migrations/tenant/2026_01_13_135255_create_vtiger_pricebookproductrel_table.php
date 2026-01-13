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
        Schema::create('vtiger_pricebookproductrel', function (Blueprint $table) {
            $table->integer('pricebookid')->index('pricebookproductrel_pricebookid_idx');
            $table->integer('productid')->index('pricebookproductrel_productid_idx');
            $table->decimal('listprice', 27, 8)->nullable();
            $table->integer('usedcurrency')->default(1);

            $table->primary(['pricebookid', 'productid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pricebookproductrel');
    }
};
