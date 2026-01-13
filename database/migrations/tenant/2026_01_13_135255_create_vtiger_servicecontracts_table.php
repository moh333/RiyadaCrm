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
        Schema::create('vtiger_servicecontracts', function (Blueprint $table) {
            $table->integer('servicecontractsid')->nullable()->index('fk_crmid_vtiger_servicecontracts');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sc_related_to')->nullable();
            $table->string('tracking_unit', 100)->nullable();
            $table->decimal('total_units', 5)->nullable();
            $table->decimal('used_units', 5)->nullable();
            $table->string('subject', 100)->nullable();
            $table->date('due_date')->nullable();
            $table->string('planned_duration', 256)->nullable();
            $table->string('actual_duration', 256)->nullable();
            $table->string('contract_status', 200)->nullable();
            $table->string('priority', 200)->nullable();
            $table->string('contract_type', 200)->nullable();
            $table->decimal('progress', 5)->nullable();
            $table->string('contract_no', 100)->nullable();
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_servicecontracts');
    }
};
