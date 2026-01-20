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
        if (Schema::hasTable('vtiger_sobillads')) {
            return;
        }
        Schema::table('vtiger_sobillads', function (Blueprint $table) {
            $table->foreign(['sobilladdressid'], 'fk_1_vtiger_sobillads')->references(['salesorderid'])->on('vtiger_salesorder')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_sobillads', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_sobillads');
        });
    }
};
