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
        if (Schema::hasTable('vtiger_wsapp_recordmapping')) {
            return;
        }
        Schema::create('vtiger_wsapp_recordmapping', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('serverid', 10)->nullable();
            $table->string('clientid')->nullable();
            $table->dateTime('clientmodifiedtime')->nullable();
            $table->integer('appid')->nullable();
            $table->dateTime('servermodifiedtime')->nullable();
            $table->integer('serverappid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_recordmapping');
    }
};
