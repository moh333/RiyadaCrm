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
        if (Schema::hasTable('vtiger_group2rs')) {
            return;
        }
        Schema::create('vtiger_group2rs', function (Blueprint $table) {
            $table->integer('groupid');
            $table->string('roleandsubid')->index('fk_2_vtiger_group2rs');

            $table->primary(['groupid', 'roleandsubid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_group2rs');
    }
};
