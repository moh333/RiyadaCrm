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
        Schema::create('vtiger_pbxmanager_phonelookup', function (Blueprint $table) {
            $table->integer('crmid')->nullable();
            $table->string('setype', 30)->nullable();
            $table->string('fnumber', 100)->nullable();
            $table->string('rnumber', 100)->nullable();
            $table->string('fieldname', 50)->nullable();

            $table->index(['fnumber', 'rnumber'], 'index_phone_number');
            $table->unique(['crmid', 'setype', 'fieldname'], 'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_pbxmanager_phonelookup');
    }
};
