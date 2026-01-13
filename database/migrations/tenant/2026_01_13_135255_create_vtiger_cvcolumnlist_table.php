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
        Schema::create('vtiger_cvcolumnlist', function (Blueprint $table) {
            $table->integer('cvid')->index('cvcolumnlist_cvid_idx');
            $table->integer('columnindex')->index('cvcolumnlist_columnindex_idx');
            $table->string('columnname', 250)->nullable()->default('');

            $table->primary(['cvid', 'columnindex']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cvcolumnlist');
    }
};
