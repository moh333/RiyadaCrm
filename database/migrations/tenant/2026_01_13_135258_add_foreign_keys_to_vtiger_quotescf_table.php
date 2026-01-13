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
        Schema::table('vtiger_quotescf', function (Blueprint $table) {
            $table->foreign(['quoteid'], 'fk_1_vtiger_quotescf')->references(['quoteid'])->on('vtiger_quotes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_quotescf', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_quotescf');
        });
    }
};
