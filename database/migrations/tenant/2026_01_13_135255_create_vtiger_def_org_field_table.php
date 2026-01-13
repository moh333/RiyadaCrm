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
        Schema::create('vtiger_def_org_field', function (Blueprint $table) {
            $table->integer('tabid')->nullable()->index('def_org_field_tabid_idx');
            $table->integer('fieldid')->primary();
            $table->integer('visible')->nullable();
            $table->integer('readonly')->nullable();

            $table->index(['tabid', 'fieldid'], 'def_org_field_tabid_fieldid_idx');
            $table->index(['visible', 'fieldid'], 'def_org_field_visible_fieldid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_def_org_field');
    }
};
