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
        Schema::create('vtiger_sostatushistory', function (Blueprint $table) {
            $table->integer('historyid', true);
            $table->integer('salesorderid')->index('sostatushistory_salesorderid_idx');
            $table->string('accountname', 100)->nullable();
            $table->decimal('total', 10, 0)->nullable();
            $table->string('sostatus', 200)->nullable();
            $table->dateTime('lastmodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_sostatushistory');
    }
};
