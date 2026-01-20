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
        if (Schema::hasTable('vtiger_salesordercf')) {
            return;
        }
        Schema::table('vtiger_salesordercf', function (Blueprint $table) {
            $table->foreign(['salesorderid'], 'fk_1_vtiger_salesordercf')->references(['salesorderid'])->on('vtiger_salesorder')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_salesordercf', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_salesordercf');
        });
    }
};
