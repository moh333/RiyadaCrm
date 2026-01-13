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
        Schema::create('vtiger_wsapp_sync_state', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 200)->nullable();
            $table->string('stateencodedvalues', 300);
            $table->integer('userid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wsapp_sync_state');
    }
};
