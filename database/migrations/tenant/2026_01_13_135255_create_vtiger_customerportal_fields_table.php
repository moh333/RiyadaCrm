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
        if (Schema::hasTable('vtiger_customerportal_fields')) {
            return;
        }
        Schema::create('vtiger_customerportal_fields', function (Blueprint $table) {
            $table->integer('tabid')->primary();
            $table->text('fieldinfo')->nullable();
            $table->integer('records_visible')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customerportal_fields');
    }
};
