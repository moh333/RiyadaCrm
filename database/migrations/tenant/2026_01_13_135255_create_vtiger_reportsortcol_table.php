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
        Schema::create('vtiger_reportsortcol', function (Blueprint $table) {
            $table->integer('sortcolid');
            $table->integer('reportid')->index('fk_1_vtiger_reportsortcol');
            $table->string('columnname', 250)->nullable()->default('');
            $table->string('sortorder', 250)->nullable()->default('Asc');

            $table->primary(['sortcolid', 'reportid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_reportsortcol');
    }
};
