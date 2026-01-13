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
        Schema::table('vtiger_quotesshipads', function (Blueprint $table) {
            $table->foreign(['quoteshipaddressid'], 'fk_1_vtiger_quotesshipads')->references(['quoteid'])->on('vtiger_quotes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_quotesshipads', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_quotesshipads');
        });
    }
};
