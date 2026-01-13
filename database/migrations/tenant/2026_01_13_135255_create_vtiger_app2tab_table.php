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
        Schema::create('vtiger_app2tab', function (Blueprint $table) {
            $table->integer('tabid')->nullable()->index('vtiger_app2tab_fk_tab');
            $table->string('appname', 20)->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('visible')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_app2tab');
    }
};
