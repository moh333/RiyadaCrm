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
        Schema::create('vtiger_blocks', function (Blueprint $table) {
            $table->integer('blockid')->primary();
            $table->integer('tabid')->index('block_tabid_idx');
            $table->string('blocklabel', 100);
            $table->integer('sequence')->nullable();
            $table->integer('show_title')->nullable();
            $table->integer('visible')->default(0);
            $table->integer('create_view')->default(0);
            $table->integer('edit_view')->default(0);
            $table->integer('detail_view')->default(0);
            $table->integer('display_status')->default(1);
            $table->integer('iscustom')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_blocks');
    }
};
