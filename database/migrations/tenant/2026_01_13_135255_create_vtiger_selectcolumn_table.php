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
        Schema::create('vtiger_selectcolumn', function (Blueprint $table) {
            $table->integer('queryid')->index('selectcolumn_queryid_idx');
            $table->integer('columnindex')->default(0);
            $table->string('columnname', 250)->nullable()->default('');

            $table->primary(['queryid', 'columnindex']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_selectcolumn');
    }
};
