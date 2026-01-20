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
        if (Schema::hasTable('vtiger_assets')) {
            return;
        }
        Schema::create('vtiger_assets', function (Blueprint $table) {
            $table->integer('assetsid')->primary();
            $table->string('asset_no', 30);
            $table->integer('account')->nullable();
            $table->integer('product');
            $table->string('serialnumber', 200)->nullable();
            $table->date('datesold')->nullable();
            $table->date('dateinservice')->nullable();
            $table->string('assetstatus', 200)->nullable()->default('In Service');
            $table->string('tagnumber', 300)->nullable();
            $table->integer('invoiceid')->nullable();
            $table->string('shippingmethod', 200)->nullable();
            $table->string('shippingtrackingnumber', 200)->nullable();
            $table->string('assetname', 100)->nullable();
            $table->integer('contact')->nullable();
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_assets');
    }
};
