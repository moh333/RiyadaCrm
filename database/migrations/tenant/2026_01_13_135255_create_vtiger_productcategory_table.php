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
        if (Schema::hasTable('vtiger_productcategory')) {
            return;
        }
        Schema::create('vtiger_productcategory', function (Blueprint $table) {
            $table->integer('productcategoryid', true);
            $table->string('productcategory', 200)->unique('productcategory_productcategory_idx');
            $table->integer('presence')->default(1);
            $table->integer('picklist_valueid')->default(0);
            $table->integer('sortorderid')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_productcategory');
    }
};
