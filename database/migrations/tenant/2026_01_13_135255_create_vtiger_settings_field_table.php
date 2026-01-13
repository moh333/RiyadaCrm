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
        Schema::create('vtiger_settings_field', function (Blueprint $table) {
            $table->integer('fieldid')->primary();
            $table->integer('blockid')->nullable()->index('fk_1_vtiger_settings_field');
            $table->string('name', 250)->nullable();
            $table->string('iconpath', 300)->nullable();
            $table->text('description')->nullable();
            $table->text('linkto')->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('active')->nullable()->default(0);
            $table->integer('pinned')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_settings_field');
    }
};
