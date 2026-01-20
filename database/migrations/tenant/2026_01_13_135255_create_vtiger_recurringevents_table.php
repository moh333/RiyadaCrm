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
        if (Schema::hasTable('vtiger_recurringevents')) {
            return;
        }
        Schema::create('vtiger_recurringevents', function (Blueprint $table) {
            $table->integer('recurringid', true);
            $table->integer('activityid')->index('fk_1_vtiger_recurringevents');
            $table->date('recurringdate')->nullable();
            $table->string('recurringtype', 30)->nullable();
            $table->integer('recurringfreq')->nullable();
            $table->string('recurringinfo', 50)->nullable();
            $table->date('recurringenddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_recurringevents');
    }
};
