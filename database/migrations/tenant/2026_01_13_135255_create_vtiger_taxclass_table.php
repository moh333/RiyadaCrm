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
        if (Schema::hasTable('vtiger_taxclass')) {
            return;
        }
        Schema::create('vtiger_taxclass', function (Blueprint $table) {
            $table->integer('taxclassid', true);
            $table->string('taxclass', 200)->unique('taxclass_carrier_idx');
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_taxclass');
    }
};
