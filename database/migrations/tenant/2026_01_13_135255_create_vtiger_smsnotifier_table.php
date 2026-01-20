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
        if (Schema::hasTable('vtiger_smsnotifier')) {
            return;
        }
        Schema::create('vtiger_smsnotifier', function (Blueprint $table) {
            $table->integer('smsnotifierid')->primary();
            $table->text('message')->nullable();
            $table->string('status', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_smsnotifier');
    }
};
