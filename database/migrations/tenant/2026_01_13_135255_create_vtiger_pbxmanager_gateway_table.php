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
        Schema::create('vtiger_pbxmanager_gateway', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('gateway', 20)->nullable();
            $table->text('parameters')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pbxmanager_gateway');
    }
};
