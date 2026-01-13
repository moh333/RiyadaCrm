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
        Schema::create('vtiger_modtracker_relations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('targetmodule', 100);
            $table->integer('targetid');
            $table->dateTime('changedon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_modtracker_relations');
    }
};
