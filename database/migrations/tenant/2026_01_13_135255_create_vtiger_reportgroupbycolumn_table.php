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
        Schema::create('vtiger_reportgroupbycolumn', function (Blueprint $table) {
            $table->integer('reportid')->nullable()->index('fk_1_vtiger_reportgroupbycolumn');
            $table->integer('sortid')->nullable();
            $table->string('sortcolname', 250)->nullable();
            $table->string('dategroupbycriteria', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportgroupbycolumn');
    }
};
