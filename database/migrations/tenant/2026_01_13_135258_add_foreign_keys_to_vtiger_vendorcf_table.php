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
        if (Schema::hasTable('vtiger_vendorcf')) {
            return;
        }
        Schema::table('vtiger_vendorcf', function (Blueprint $table) {
            $table->foreign(['vendorid'], 'fk_1_vtiger_vendorcf')->references(['vendorid'])->on('vtiger_vendor')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_vendorcf', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_vendorcf');
        });
    }
};
