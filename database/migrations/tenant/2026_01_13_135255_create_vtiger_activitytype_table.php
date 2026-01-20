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
        if (Schema::hasTable('vtiger_activitytype')) {
            return;
        }
        Schema::create('vtiger_activitytype', function (Blueprint $table) {
            $table->integer('activitytypeid', true);
            $table->string('activitytype', 200)->unique('activitytype_activitytype_idx');
            $table->integer('presence')->default(1);
            $table->integer('picklist_valueid')->default(0);
            $table->integer('sortorderid')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activitytype');
    }
};
