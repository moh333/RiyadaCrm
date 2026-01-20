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
        if (Schema::hasTable('vtiger_accountbillads')) {
            return;
        }
        Schema::table('vtiger_accountbillads', function (Blueprint $table) {
            $table->foreign(['accountaddressid'], 'fk_1_vtiger_accountbillads')->references(['accountid'])->on('vtiger_account')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_accountbillads', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_accountbillads');
        });
    }
};
