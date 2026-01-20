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
        if (Schema::hasTable('vtiger_defaultcv')) {
            return;
        }
        Schema::create('vtiger_defaultcv', function (Blueprint $table) {
            $table->integer('tabid')->primary();
            $table->string('defaultviewname', 50);
            $table->text('query')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_defaultcv');
    }
};
