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
        Schema::create('vtiger_campaignaccountrel', function (Blueprint $table) {
            $table->integer('campaignid')->nullable();
            $table->integer('accountid')->nullable();
            $table->integer('campaignrelstatusid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_campaignaccountrel');
    }
};
