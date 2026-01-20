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
        if (Schema::hasTable('vtiger_parenttabrel')) {
            return;
        }
        Schema::create('vtiger_parenttabrel', function (Blueprint $table) {
            $table->integer('parenttabid')->index('fk_2_vtiger_parenttabrel');
            $table->integer('tabid');
            $table->integer('sequence');

            $table->index(['tabid', 'parenttabid'], 'parenttabrel_tabid_parenttabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_parenttabrel');
    }
};
