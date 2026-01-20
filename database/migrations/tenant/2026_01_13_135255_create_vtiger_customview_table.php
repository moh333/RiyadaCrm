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
        if (Schema::hasTable('vtiger_customview')) {
            return;
        }
        Schema::create('vtiger_customview', function (Blueprint $table) {
            $table->integer('cvid')->primary();
            $table->string('viewname', 100);
            $table->integer('setdefault')->nullable()->default(0);
            $table->integer('setmetrics')->nullable()->default(0);
            $table->string('entitytype', 25)->index('customview_entitytype_idx');
            $table->integer('status')->nullable()->default(1);
            $table->integer('userid')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customview');
    }
};
