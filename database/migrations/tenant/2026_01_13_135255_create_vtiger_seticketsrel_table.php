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
        if (Schema::hasTable('vtiger_seticketsrel')) {
            return;
        }
        Schema::create('vtiger_seticketsrel', function (Blueprint $table) {
            $table->integer('crmid')->default(0)->index('seticketsrel_crmid_idx');
            $table->integer('ticketid')->default(0)->index('seticketsrel_ticketid_idx');

            $table->primary(['crmid', 'ticketid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_seticketsrel');
    }
};
