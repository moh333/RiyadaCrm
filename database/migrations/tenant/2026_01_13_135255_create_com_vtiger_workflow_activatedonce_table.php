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
        if (Schema::hasTable('com_vtiger_workflow_activatedonce')) {
            return;
        }
        Schema::create('com_vtiger_workflow_activatedonce', function (Blueprint $table) {
            $table->integer('workflow_id');
            $table->integer('entity_id');

            $table->primary(['workflow_id', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vtiger_workflow_activatedonce');
    }
};
