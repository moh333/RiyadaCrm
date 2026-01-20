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
        if (Schema::hasTable('vtiger_quotestagehistory')) {
            return;
        }
        Schema::create('vtiger_quotestagehistory', function (Blueprint $table) {
            $table->integer('historyid', true);
            $table->integer('quoteid')->index('quotestagehistory_quoteid_idx');
            $table->string('accountname', 100)->nullable();
            $table->decimal('total', 10, 0)->nullable();
            $table->string('quotestage', 200)->nullable();
            $table->dateTime('lastmodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_quotestagehistory');
    }
};
