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
        if (Schema::hasTable('vtiger_ticketcomments')) {
            return;
        }
        Schema::table('vtiger_ticketcomments', function (Blueprint $table) {
            $table->foreign(['ticketid'], 'fk_1_vtiger_ticketcomments')->references(['ticketid'])->on('vtiger_troubletickets')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_ticketcomments', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_ticketcomments');
        });
    }
};
