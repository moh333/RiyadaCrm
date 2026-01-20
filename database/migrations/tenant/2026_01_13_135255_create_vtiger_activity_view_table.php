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
        if (Schema::hasTable('vtiger_activity_view')) {
            return;
        }
        Schema::create('vtiger_activity_view', function (Blueprint $table) {
            $table->integer('activity_viewid', true);
            $table->string('activity_view', 200);
            $table->integer('sortorderid')->default(0);
            $table->integer('presence')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activity_view');
    }
};
