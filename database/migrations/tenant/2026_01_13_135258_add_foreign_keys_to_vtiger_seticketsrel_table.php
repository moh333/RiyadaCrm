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
        if (Schema::hasTable('vtiger_seticketsrel')) {
            return;
        }
        Schema::table('vtiger_seticketsrel', function (Blueprint $table) {
            $table->foreign(['ticketid'], 'fk_2_vtiger_seticketsrel')->references(['ticketid'])->on('vtiger_troubletickets')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_seticketsrel', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_seticketsrel');
        });
    }
};
