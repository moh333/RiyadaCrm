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
        if (Schema::hasTable('vtiger_inventorychargesrel')) {
            return;
        }
        Schema::create('vtiger_inventorychargesrel', function (Blueprint $table) {
            $table->integer('recordid')->index('record_idx');
            $table->text('charges')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventorychargesrel');
    }
};
