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
        if (Schema::hasTable('com_vtiger_workflowtemplates')) {
            return;
        }
        Schema::create('com_vtiger_workflowtemplates', function (Blueprint $table) {
            $table->integer('template_id', true);
            $table->string('module_name', 100)->nullable();
            $table->string('title', 400)->nullable();
            $table->text('template')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflowtemplates');
    }
};
