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
        if (Schema::hasTable('vtiger_potstagehistory')) {
            return;
        }
        Schema::create('vtiger_potstagehistory', function (Blueprint $table) {
            $table->integer('historyid', true);
            $table->integer('potentialid')->index('potstagehistory_potentialid_idx');
            $table->decimal('amount', 10, 0)->nullable();
            $table->string('stage', 100)->nullable();
            $table->decimal('probability', 7, 3)->nullable();
            $table->decimal('expectedrevenue', 10, 0)->nullable();
            $table->date('closedate')->nullable();
            $table->dateTime('lastmodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_potstagehistory');
    }
};
