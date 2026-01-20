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
        if (Schema::hasTable('vtiger_relcriteria')) {
            return;
        }
        Schema::table('vtiger_relcriteria', function (Blueprint $table) {
            $table->foreign(['queryid'], 'fk_1_vtiger_relcriteria')->references(['queryid'])->on('vtiger_selectquery')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_relcriteria', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_relcriteria');
        });
    }
};
