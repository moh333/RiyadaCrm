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
        Schema::create('vtiger_pbxmanager', function (Blueprint $table) {
            $table->integer('pbxmanagerid', true)->index('index_pbxmanager_id');
            $table->string('direction', 10)->nullable();
            $table->string('callstatus', 20)->nullable();
            $table->dateTime('starttime')->nullable();
            $table->dateTime('endtime')->nullable();
            $table->integer('totalduration')->nullable();
            $table->integer('billduration')->nullable();
            $table->string('recordingurl', 200)->nullable();
            $table->string('sourceuuid', 100)->nullable()->index('index_sourceuuid');
            $table->string('gateway', 20)->nullable();
            $table->string('customer', 100)->nullable();
            $table->string('user', 100)->nullable();
            $table->string('customernumber', 100)->nullable();
            $table->string('customertype', 100)->nullable();

            $table->primary(['pbxmanagerid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pbxmanager');
    }
};
