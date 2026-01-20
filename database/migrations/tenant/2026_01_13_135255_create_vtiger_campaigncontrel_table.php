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
        if (Schema::hasTable('vtiger_campaigncontrel')) {
            return;
        }
        Schema::create('vtiger_campaigncontrel', function (Blueprint $table) {
            $table->integer('campaignid')->default(0);
            $table->integer('contactid')->default(0)->index('campaigncontrel_contractid_idx');
            $table->integer('campaignrelstatusid')->default(0);

            $table->primary(['campaignid', 'contactid', 'campaignrelstatusid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_campaigncontrel');
    }
};
