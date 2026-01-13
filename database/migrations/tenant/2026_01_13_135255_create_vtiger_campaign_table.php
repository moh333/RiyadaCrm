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
        Schema::create('vtiger_campaign', function (Blueprint $table) {
            $table->string('campaign_no', 100);
            $table->string('campaignname')->nullable()->index('campaign_campaignname_idx');
            $table->string('campaigntype', 200)->nullable();
            $table->string('campaignstatus', 200)->nullable()->index('campaign_campaignstatus_idx');
            $table->decimal('expectedrevenue', 25, 8)->nullable();
            $table->decimal('budgetcost', 25, 8)->nullable();
            $table->decimal('actualcost', 25, 8)->nullable();
            $table->string('expectedresponse', 200)->nullable();
            $table->decimal('numsent', 11, 0)->nullable();
            $table->integer('product_id')->nullable();
            $table->string('sponsor')->nullable();
            $table->string('targetaudience')->nullable();
            $table->integer('targetsize')->nullable();
            $table->integer('expectedresponsecount')->nullable();
            $table->integer('expectedsalescount')->nullable();
            $table->decimal('expectedroi', 25, 8)->nullable();
            $table->integer('actualresponsecount')->nullable();
            $table->integer('actualsalescount')->nullable();
            $table->decimal('actualroi', 25, 8)->nullable();
            $table->integer('campaignid')->index('campaign_campaignid_idx');
            $table->date('closingdate')->nullable();
            $table->string('tags', 1)->nullable();

            $table->primary(['campaignid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_campaign');
    }
};
