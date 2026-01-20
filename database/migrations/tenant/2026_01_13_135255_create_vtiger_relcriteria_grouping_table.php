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
        if (Schema::hasTable('vtiger_relcriteria_grouping')) {
            return;
        }
        Schema::create('vtiger_relcriteria_grouping', function (Blueprint $table) {
            $table->integer('groupid');
            $table->integer('queryid');
            $table->string('group_condition', 256)->nullable();
            $table->text('condition_expression')->nullable();

            $table->primary(['groupid', 'queryid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_relcriteria_grouping');
    }
};
