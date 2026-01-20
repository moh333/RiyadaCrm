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
        if (Schema::hasTable('vtiger_shareduserinfo')) {
            return;
        }
        Schema::create('vtiger_shareduserinfo', function (Blueprint $table) {
            $table->integer('userid')->default(0);
            $table->integer('shareduserid')->default(0);
            $table->string('color', 50)->nullable();
            $table->integer('visible')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_shareduserinfo');
    }
};
