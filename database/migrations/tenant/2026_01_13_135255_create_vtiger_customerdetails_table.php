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
        Schema::create('vtiger_customerdetails', function (Blueprint $table) {
            $table->integer('customerid')->primary();
            $table->string('portal', 3)->nullable();
            $table->date('support_start_date')->nullable();
            $table->date('support_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customerdetails');
    }
};
