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
        Schema::create('vtiger_payment_duration', function (Blueprint $table) {
            $table->integer('payment_duration_id')->nullable();
            $table->string('payment_duration', 200)->nullable();
            $table->integer('sortorderid')->nullable();
            $table->integer('presence')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_payment_duration');
    }
};
