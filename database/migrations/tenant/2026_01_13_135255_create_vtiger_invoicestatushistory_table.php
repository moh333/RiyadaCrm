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
        if (Schema::hasTable('vtiger_invoicestatushistory')) {
            return;
        }
        Schema::create('vtiger_invoicestatushistory', function (Blueprint $table) {
            $table->integer('historyid', true);
            $table->integer('invoiceid')->index('invoicestatushistory_invoiceid_idx');
            $table->string('accountname', 100)->nullable();
            $table->decimal('total', 10, 0)->nullable();
            $table->string('invoicestatus', 200)->nullable();
            $table->dateTime('lastmodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_invoicestatushistory');
    }
};
