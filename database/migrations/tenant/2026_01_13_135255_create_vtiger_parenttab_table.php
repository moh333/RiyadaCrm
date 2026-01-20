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
        if (Schema::hasTable('vtiger_parenttab')) {
            return;
        }
        Schema::create('vtiger_parenttab', function (Blueprint $table) {
            $table->integer('parenttabid')->primary();
            $table->string('parenttab_label', 100);
            $table->integer('sequence');
            $table->integer('visible')->default(0);

            $table->index(['parenttabid', 'parenttab_label', 'visible'], 'parenttab_parenttabid_parenttabl_label_visible_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_parenttab');
    }
};
