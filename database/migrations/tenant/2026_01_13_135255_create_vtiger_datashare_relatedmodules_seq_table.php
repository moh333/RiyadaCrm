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
        if (Schema::hasTable('vtiger_datashare_relatedmodules_seq')) {
            return;
        }
        Schema::create('vtiger_datashare_relatedmodules_seq', function (Blueprint $table) {
            $table->integer('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_datashare_relatedmodules_seq');
    }
};
