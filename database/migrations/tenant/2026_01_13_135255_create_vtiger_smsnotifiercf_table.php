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
        if (Schema::hasTable('vtiger_smsnotifiercf')) {
            return;
        }
        Schema::create('vtiger_smsnotifiercf', function (Blueprint $table) {
            $table->integer('smsnotifierid')->primary();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_smsnotifiercf');
    }
};
