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
        if (Schema::hasTable('vtiger_freetags')) {
            return;
        }
        Schema::create('vtiger_freetags', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('tag', 50)->default('');
            $table->string('raw_tag', 50)->default('');
            $table->string('visibility', 100)->default('PRIVATE');
            $table->integer('owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_freetags');
    }
};
