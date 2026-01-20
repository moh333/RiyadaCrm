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
        if (Schema::hasTable('vtiger_poshipads')) {
            return;
        }
        Schema::create('vtiger_poshipads', function (Blueprint $table) {
            $table->integer('poshipaddressid')->default(0)->primary();
            $table->string('ship_city', 30)->nullable();
            $table->string('ship_code', 30)->nullable();
            $table->string('ship_country', 30)->nullable();
            $table->string('ship_state', 30)->nullable();
            $table->string('ship_street', 250)->nullable();
            $table->string('ship_pobox', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_poshipads');
    }
};
