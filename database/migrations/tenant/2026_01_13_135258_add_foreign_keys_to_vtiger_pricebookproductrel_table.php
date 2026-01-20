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
        if (Schema::hasTable('vtiger_pricebookproductrel')) {
            return;
        }
        Schema::table('vtiger_pricebookproductrel', function (Blueprint $table) {
            $table->foreign(['pricebookid'], 'fk_1_vtiger_pricebookproductrel')->references(['pricebookid'])->on('vtiger_pricebook')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_pricebookproductrel', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_pricebookproductrel');
        });
    }
};
