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
        Schema::create('vtiger_wsapp_logs_details', function (Blueprint $table) {
            $table->integer('id')->index('vtiger_wsapp_logs_basic_ibfk_1');
            $table->mediumText('app_create_ids')->nullable();
            $table->mediumText('app_update_ids')->nullable();
            $table->mediumText('app_delete_ids')->nullable();
            $table->mediumText('app_skip_info')->nullable();
            $table->mediumText('vt_create_ids')->nullable();
            $table->mediumText('vt_update_ids')->nullable();
            $table->mediumText('vt_delete_ids')->nullable();
            $table->mediumText('vt_skip_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_logs_details');
    }
};
