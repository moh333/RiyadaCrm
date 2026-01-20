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
        if (Schema::hasTable('vtiger_module_dashboard_widgets')) {
            return;
        }
        Schema::create('vtiger_module_dashboard_widgets', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('linkid')->nullable();
            $table->integer('userid')->nullable();
            $table->integer('filterid')->nullable();
            $table->string('title', 100)->nullable();
            $table->text('data')->nullable();
            $table->string('position', 50)->nullable();
            $table->integer('reportid')->nullable();
            $table->integer('dashboardtabid')->nullable()->index('dashboardtabid');
            $table->string('size', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_module_dashboard_widgets');
    }
};
