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
        if (Schema::hasTable('vtiger_potential')) {
            return;
        }
        Schema::create('vtiger_potential', function (Blueprint $table) {
            $table->integer('potentialid')->default(0)->primary();
            $table->string('potential_no', 100);
            $table->integer('related_to')->nullable()->index('potential_relatedto_idx');
            $table->string('potentialname', 120);
            $table->decimal('amount', 25, 8)->nullable();
            $table->string('currency', 20)->nullable();
            $table->date('closingdate')->nullable();
            $table->string('typeofrevenue', 50)->nullable();
            $table->string('nextstep', 100)->nullable();
            $table->integer('private')->nullable()->default(0);
            $table->decimal('probability', 7, 3)->nullable()->default(0);
            $table->integer('campaignid')->nullable();
            $table->string('sales_stage', 200)->nullable()->index('potentail_sales_stage_idx');
            $table->string('potentialtype', 200)->nullable();
            $table->string('leadsource', 200)->nullable();
            $table->integer('productid')->nullable();
            $table->string('productversion', 50)->nullable();
            $table->string('quotationref', 50)->nullable();
            $table->string('partnercontact', 50)->nullable();
            $table->string('remarks', 50)->nullable();
            $table->integer('runtimefee')->nullable()->default(0);
            $table->date('followupdate')->nullable();
            $table->string('evaluationstatus', 50)->nullable();
            $table->text('description')->nullable();
            $table->integer('forecastcategory')->nullable()->default(0);
            $table->integer('outcomeanalysis')->nullable()->default(0);
            $table->decimal('forecast_amount', 25, 8)->nullable();
            $table->string('isconvertedfromlead', 3)->nullable()->default('0');
            $table->integer('contact_id')->nullable();
            $table->string('tags', 1)->nullable();
            $table->integer('converted')->default(0);

            $table->index(['amount', 'sales_stage'], 'potentail_sales_stage_amount_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_potential');
    }
};
