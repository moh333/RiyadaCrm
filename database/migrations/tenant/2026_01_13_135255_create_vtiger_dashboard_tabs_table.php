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
        if (Schema::hasTable('vtiger_dashboard_tabs')) {
            return;
        }
        Schema::create('vtiger_dashboard_tabs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('tabname', 50)->nullable();
            $table->integer('isdefault')->nullable()->default(0);
            $table->integer('sequence')->nullable()->default(2);
            $table->string('appname', 20)->nullable();
            $table->string('modulename', 50)->nullable();
            $table->integer('userid')->nullable()->index('vtiger_dashboard_tabs_ibfk_1');

            $table->unique(['tabname', 'userid'], 'tabname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_dashboard_tabs');
    }
};
