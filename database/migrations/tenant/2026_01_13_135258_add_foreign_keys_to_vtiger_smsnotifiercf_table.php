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
        if (Schema::hasTable('vtiger_smsnotifiercf')) {
            return;
        }
        Schema::table('vtiger_smsnotifiercf', function (Blueprint $table) {
            $table->foreign(['smsnotifierid'], 'fk_smsnotifierid_vtiger_smsnotifiercf')->references(['smsnotifierid'])->on('vtiger_smsnotifier')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_smsnotifiercf', function (Blueprint $table) {
            $table->dropForeign('fk_smsnotifierid_vtiger_smsnotifiercf');
        });
    }
};
