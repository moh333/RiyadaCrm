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
        Schema::create('vtiger_seproductsrel', function (Blueprint $table) {
            $table->integer('crmid')->default(0)->index('seproductrel_crmid_idx');
            $table->integer('productid')->default(0)->index('seproductsrel_productid_idx');
            $table->string('setype', 30);
            $table->integer('quantity')->nullable()->default(1);

            $table->primary(['crmid', 'productid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_seproductsrel');
    }
};
