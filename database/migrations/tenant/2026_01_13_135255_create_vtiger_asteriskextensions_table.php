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
        Schema::create('vtiger_asteriskextensions', function (Blueprint $table) {
            $table->integer('userid')->nullable();
            $table->string('asterisk_extension', 50)->nullable();
            $table->string('use_asterisk', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_asteriskextensions');
    }
};
