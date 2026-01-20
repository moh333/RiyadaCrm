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
        if (Schema::hasTable('vtiger_portal')) {
            return;
        }
        Schema::create('vtiger_portal', function (Blueprint $table) {
            $table->integer('portalid')->primary();
            $table->string('portalname', 200)->index('portal_portalname_idx');
            $table->string('portalurl');
            $table->integer('sequence');
            $table->integer('setdefault')->default(0);
            $table->dateTime('createdtime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_portal');
    }
};
