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
        Schema::create('vtiger_rss', function (Blueprint $table) {
            $table->integer('rssid')->primary();
            $table->string('rssurl', 200)->default('');
            $table->string('rsstitle', 200)->nullable();
            $table->integer('rsstype')->nullable()->default(0);
            $table->integer('starred')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_rss');
    }
};
