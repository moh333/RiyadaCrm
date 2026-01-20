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
        if (Schema::hasTable('vtiger_projecttaskpriority')) {
            return;
        }
        Schema::create('vtiger_projecttaskpriority', function (Blueprint $table) {
            $table->integer('projecttaskpriorityid', true);
            $table->string('projecttaskpriority', 200);
            $table->integer('presence')->default(1);
            $table->integer('picklist_valueid')->default(0);
            $table->integer('sortorderid')->nullable()->default(0);
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_projecttaskpriority');
    }
};
