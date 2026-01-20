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
        if (Schema::hasTable('vtiger_soapservice')) {
            return;
        }
        Schema::create('vtiger_soapservice', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('type', 25)->nullable();
            $table->string('sessionid', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_soapservice');
    }
};
