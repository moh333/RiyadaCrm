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
        if (Schema::hasTable('vtiger_homestuff')) {
            return;
        }
        Schema::create('vtiger_homestuff', function (Blueprint $table) {
            $table->integer('stuffid')->default(0)->primary();
            $table->integer('stuffsequence')->default(0);
            $table->string('stufftype', 100)->nullable();
            $table->integer('userid')->index('fk_1_vtiger_homestuff');
            $table->integer('visible')->default(0);
            $table->string('stufftitle', 100)->nullable();

            $table->index(['stuffid'], 'stuff_stuffid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homestuff');
    }
};
