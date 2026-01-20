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
        if (Schema::hasTable('vtiger_projecttaskstatus_seq')) {
            return;
        }
        Schema::create('vtiger_projecttaskstatus_seq', function (Blueprint $table) {
            $table->integer('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_projecttaskstatus_seq');
    }
};
