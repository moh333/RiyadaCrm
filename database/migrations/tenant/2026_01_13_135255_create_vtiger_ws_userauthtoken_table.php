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
        Schema::create('vtiger_ws_userauthtoken', function (Blueprint $table) {
            $table->integer('userid')->unique('userid_idx');
            $table->string('token', 36);
            $table->integer('expiretime');

            $table->primary(['userid', 'expiretime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ws_userauthtoken');
    }
};
