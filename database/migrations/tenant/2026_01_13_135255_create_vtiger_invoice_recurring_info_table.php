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
        Schema::create('vtiger_invoice_recurring_info', function (Blueprint $table) {
            $table->integer('salesorderid')->primary();
            $table->string('recurring_frequency', 200)->nullable();
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->date('last_recurring_date')->nullable();
            $table->string('payment_duration', 200)->nullable();
            $table->string('invoice_status', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_invoice_recurring_info');
    }
};
