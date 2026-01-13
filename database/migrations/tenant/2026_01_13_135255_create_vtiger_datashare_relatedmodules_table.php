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
        Schema::create('vtiger_datashare_relatedmodules', function (Blueprint $table) {
            $table->integer('datashare_relatedmodule_id')->primary();
            $table->integer('tabid')->nullable()->index('datashare_relatedmodules_tabid_idx');
            $table->integer('relatedto_tabid')->nullable()->index('datashare_relatedmodules_relatedto_tabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_relatedmodules');
    }
};
