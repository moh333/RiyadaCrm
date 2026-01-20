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
        if (Schema::hasTable('vtiger_currency_grouping_pattern')) {
            return;
        }
        Schema::create('vtiger_currency_grouping_pattern', function (Blueprint $table) {
            $table->integer('currency_grouping_patternid', true);
            $table->string('currency_grouping_pattern', 200);
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_currency_grouping_pattern');
    }
};
