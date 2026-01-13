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
        Schema::create('vtiger_rollupcomments_settings', function (Blueprint $table) {
            $table->integer('rollupid', true);
            $table->integer('userid');
            $table->integer('tabid');
            $table->integer('rollup_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_rollupcomments_settings');
    }
};
