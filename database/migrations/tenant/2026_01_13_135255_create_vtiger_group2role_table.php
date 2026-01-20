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
        if (Schema::hasTable('vtiger_group2role')) {
            return;
        }
        Schema::create('vtiger_group2role', function (Blueprint $table) {
            $table->integer('groupid');
            $table->string('roleid')->index('fk_2_vtiger_group2role');

            $table->primary(['groupid', 'roleid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_group2role');
    }
};
