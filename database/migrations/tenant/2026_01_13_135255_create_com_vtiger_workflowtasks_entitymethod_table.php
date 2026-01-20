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
        if (Schema::hasTable('com_vtiger_workflowtasks_entitymethod')) {
            return;
        }
        Schema::create('com_vtiger_workflowtasks_entitymethod', function (Blueprint $table) {
            $table->integer('workflowtasks_entitymethod_id')->unique('com_vtiger_workflowtasks_entitymethod_idx');
            $table->string('module_name', 100)->nullable();
            $table->string('method_name', 100)->nullable();
            $table->string('function_path', 400)->nullable();
            $table->string('function_name', 100)->nullable();

            $table->primary(['workflowtasks_entitymethod_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflowtasks_entitymethod');
    }
};
