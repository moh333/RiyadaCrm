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
        if (Schema::hasTable('vtiger_group2grouprel')) {
            return;
        }
        Schema::create('vtiger_group2grouprel', function (Blueprint $table) {
            $table->integer('groupid');
            $table->integer('containsgroupid');

            $table->primary(['groupid', 'containsgroupid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_group2grouprel');
    }
};
