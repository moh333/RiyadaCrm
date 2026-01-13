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
        Schema::create('vtiger_campaignleadrel', function (Blueprint $table) {
            $table->integer('campaignid')->default(0);
            $table->integer('leadid')->default(0);
            $table->integer('campaignrelstatusid')->default(0);

            $table->index(['leadid', 'campaignid'], 'campaignleadrel_leadid_campaignid_idx');
            $table->primary(['campaignid', 'leadid', 'campaignrelstatusid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_campaignleadrel');
    }
};
