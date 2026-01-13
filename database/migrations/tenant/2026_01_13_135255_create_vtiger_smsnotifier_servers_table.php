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
        Schema::create('vtiger_smsnotifier_servers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('password')->nullable();
            $table->integer('isactive')->nullable();
            $table->string('providertype', 50)->nullable();
            $table->string('username')->nullable();
            $table->text('parameters')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_smsnotifier_servers');
    }
};
