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
        if (Schema::hasTable('vtiger_mobile_alerts')) {
            return;
        }
        Schema::create('vtiger_mobile_alerts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('handler_path', 500)->nullable();
            $table->string('handler_class', 50)->nullable();
            $table->integer('sequence')->nullable();
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mobile_alerts');
    }
};
