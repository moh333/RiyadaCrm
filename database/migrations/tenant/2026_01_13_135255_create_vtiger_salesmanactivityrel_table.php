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
        Schema::create('vtiger_salesmanactivityrel', function (Blueprint $table) {
            $table->integer('smid')->default(0)->index('salesmanactivityrel_smid_idx');
            $table->integer('activityid')->default(0)->index('salesmanactivityrel_activityid_idx');

            $table->primary(['smid', 'activityid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_salesmanactivityrel');
    }
};
