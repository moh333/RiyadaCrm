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
        Schema::create('vtiger_inventorycharges', function (Blueprint $table) {
            $table->integer('chargeid', true);
            $table->string('name', 100);
            $table->string('format', 10)->nullable();
            $table->string('type', 10)->nullable();
            $table->decimal('value', 12, 5)->nullable();
            $table->text('regions')->nullable();
            $table->integer('istaxable')->default(1);
            $table->string('taxes', 1024)->nullable();
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_inventorycharges');
    }
};
