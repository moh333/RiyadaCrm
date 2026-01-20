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
        if (Schema::hasTable('vtiger_picklist_dependency')) {
            return;
        }
        Schema::create('vtiger_picklist_dependency', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('tabid');
            $table->string('sourcefield')->nullable();
            $table->string('targetfield')->nullable();
            $table->string('sourcevalue', 100)->nullable();
            $table->text('targetvalues')->nullable();
            $table->text('criteria')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_picklist_dependency');
    }
};
