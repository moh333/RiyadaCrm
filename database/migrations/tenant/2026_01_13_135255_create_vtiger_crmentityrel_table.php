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
        if (Schema::hasTable('vtiger_crmentityrel')) {
            return;
        }
        Schema::create('vtiger_crmentityrel', function (Blueprint $table) {
            $table->integer('crmid')->index('crmid_idx');
            $table->string('module', 100);
            $table->integer('relcrmid')->index('relcrmid_idx');
            $table->string('relmodule', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_crmentityrel');
    }
};
