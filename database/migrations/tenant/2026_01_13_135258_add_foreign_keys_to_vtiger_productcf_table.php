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
        if (Schema::hasTable('vtiger_productcf')) {
            return;
        }
        Schema::table('vtiger_productcf', function (Blueprint $table) {
            $table->foreign(['productid'], 'fk_1_vtiger_productcf')->references(['productid'])->on('vtiger_products')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_productcf', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_productcf');
        });
    }
};
