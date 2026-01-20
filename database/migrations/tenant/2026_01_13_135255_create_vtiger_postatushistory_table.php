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
        if (Schema::hasTable('vtiger_postatushistory')) {
            return;
        }
        Schema::create('vtiger_postatushistory', function (Blueprint $table) {
            $table->integer('historyid', true);
            $table->integer('purchaseorderid')->index('postatushistory_purchaseorderid_idx');
            $table->string('vendorname', 100)->nullable();
            $table->decimal('total', 10, 0)->nullable();
            $table->string('postatus', 200)->nullable();
            $table->dateTime('lastmodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_postatushistory');
    }
};
