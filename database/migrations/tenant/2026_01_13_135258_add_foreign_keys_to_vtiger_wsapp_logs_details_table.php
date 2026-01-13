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
        Schema::table('vtiger_wsapp_logs_details', function (Blueprint $table) {
            $table->foreign(['id'], 'vtiger_wsapp_logs_basic_ibfk_1')->references(['id'])->on('vtiger_wsapp_logs_basic')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_wsapp_logs_details', function (Blueprint $table) {
            $table->dropForeign('vtiger_wsapp_logs_basic_ibfk_1');
        });
    }
};
