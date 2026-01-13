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
        Schema::create('vtiger_links', function (Blueprint $table) {
            $table->integer('linkid')->primary();
            $table->integer('tabid')->nullable();
            $table->string('linktype', 50)->nullable();
            $table->string('linklabel', 50)->nullable();
            $table->string('linkurl')->nullable();
            $table->string('linkicon', 100)->nullable();
            $table->integer('sequence')->nullable();
            $table->string('handler_path', 128)->nullable();
            $table->string('handler_class', 50)->nullable();
            $table->string('handler', 50)->nullable();
            $table->integer('parent_link')->nullable();

            $table->index(['tabid', 'linktype'], 'link_tabidtype_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_links');
    }
};
