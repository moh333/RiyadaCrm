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
        if (Schema::hasTable('vtiger_def_org_share')) {
            return;
        }
        Schema::create('vtiger_def_org_share', function (Blueprint $table) {
            $table->integer('ruleid', true);
            $table->integer('tabid');
            $table->integer('permission')->nullable()->index('fk_1_vtiger_def_org_share');
            $table->integer('editstatus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_def_org_share');
    }
};
