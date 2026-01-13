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
        Schema::create('vtiger_tab_info', function (Blueprint $table) {
            $table->integer('tabid')->nullable()->index('fk_1_vtiger_tab_info');
            $table->string('prefname', 256)->nullable();
            $table->string('prefvalue', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_tab_info');
    }
};
