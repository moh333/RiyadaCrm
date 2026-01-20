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
        if (Schema::hasTable('vtiger_smsnotifier_status')) {
            return;
        }
        Schema::create('vtiger_smsnotifier_status', function (Blueprint $table) {
            $table->integer('smsnotifierid')->nullable();
            $table->string('tonumber', 20)->nullable();
            $table->string('status', 10)->nullable();
            $table->string('smsmessageid', 50)->nullable();
            $table->integer('needlookup')->nullable()->default(1);
            $table->integer('statusid', true);
            $table->string('statusmessage', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_smsnotifier_status');
    }
};
