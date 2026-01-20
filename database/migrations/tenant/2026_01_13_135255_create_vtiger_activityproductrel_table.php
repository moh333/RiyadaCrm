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
        if (Schema::hasTable('vtiger_activityproductrel')) {
            return;
        }
        Schema::create('vtiger_activityproductrel', function (Blueprint $table) {
            $table->integer('activityid')->default(0)->index('activityproductrel_activityid_idx');
            $table->integer('productid')->default(0)->index('activityproductrel_productid_idx');

            $table->primary(['activityid', 'productid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_activityproductrel');
    }
};
