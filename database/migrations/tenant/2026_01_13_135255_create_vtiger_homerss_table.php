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
        if (Schema::hasTable('vtiger_homerss')) {
            return;
        }
        Schema::create('vtiger_homerss', function (Blueprint $table) {
            $table->integer('stuffid')->default(0)->primary();
            $table->string('url', 100)->nullable();
            $table->integer('maxentries');

            $table->index(['stuffid'], 'stuff_stuffid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homerss');
    }
};
