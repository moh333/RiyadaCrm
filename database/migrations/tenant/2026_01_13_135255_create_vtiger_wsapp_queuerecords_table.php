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
        if (Schema::hasTable('vtiger_wsapp_queuerecords')) {
            return;
        }
        Schema::create('vtiger_wsapp_queuerecords', function (Blueprint $table) {
            $table->integer('syncserverid')->nullable();
            $table->string('details', 300)->nullable();
            $table->string('flag', 100)->nullable();
            $table->integer('appid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_queuerecords');
    }
};
