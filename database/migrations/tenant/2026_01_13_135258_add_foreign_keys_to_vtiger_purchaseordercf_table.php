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
        if (Schema::hasTable('vtiger_purchaseordercf')) {
            return;
        }
        Schema::table('vtiger_purchaseordercf', function (Blueprint $table) {
            $table->foreign(['purchaseorderid'], 'fk_1_vtiger_purchaseordercf')->references(['purchaseorderid'])->on('vtiger_purchaseorder')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_purchaseordercf', function (Blueprint $table) {
            $table->dropForeign('fk_1_vtiger_purchaseordercf');
        });
    }
};
