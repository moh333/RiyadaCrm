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
        if (Schema::hasTable('vtiger_cvstdfilter')) {
            return;
        }
        Schema::create('vtiger_cvstdfilter', function (Blueprint $table) {
            $table->integer('cvid')->index('cvstdfilter_cvid_idx');
            $table->string('columnname', 250)->nullable()->default('');
            $table->string('stdfilter', 250)->nullable()->default('');
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_cvstdfilter');
    }
};
