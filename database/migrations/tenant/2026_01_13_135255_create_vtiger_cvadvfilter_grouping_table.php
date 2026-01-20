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
        if (Schema::hasTable('vtiger_cvadvfilter_grouping')) {
            return;
        }
        Schema::create('vtiger_cvadvfilter_grouping', function (Blueprint $table) {
            $table->integer('groupid');
            $table->integer('cvid');
            $table->string('group_condition')->nullable();
            $table->text('condition_expression')->nullable();

            $table->primary(['groupid', 'cvid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cvadvfilter_grouping');
    }
};
