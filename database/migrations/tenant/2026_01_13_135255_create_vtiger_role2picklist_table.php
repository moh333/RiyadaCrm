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
        Schema::create('vtiger_role2picklist', function (Blueprint $table) {
            $table->string('roleid');
            $table->integer('picklistvalueid');
            $table->integer('picklistid')->index('fk_2_vtiger_role2picklist');
            $table->integer('sortid')->nullable();

            $table->primary(['roleid', 'picklistvalueid', 'picklistid']);
            $table->index(['roleid', 'picklistid', 'picklistvalueid'], 'role2picklist_roleid_picklistid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_role2picklist');
    }
};
