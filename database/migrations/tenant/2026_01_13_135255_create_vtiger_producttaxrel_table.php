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
        Schema::create('vtiger_producttaxrel', function (Blueprint $table) {
            $table->integer('productid')->index('producttaxrel_productid_idx');
            $table->integer('taxid')->index('producttaxrel_taxid_idx');
            $table->decimal('taxpercentage', 7, 3)->nullable();
            $table->text('regions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_producttaxrel');
    }
};
