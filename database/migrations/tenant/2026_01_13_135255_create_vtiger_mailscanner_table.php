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
        Schema::create('vtiger_mailscanner', function (Blueprint $table) {
            $table->integer('scannerid', true);
            $table->string('scannername', 30)->nullable();
            $table->string('server', 100)->nullable();
            $table->string('protocol', 10)->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('ssltype', 10)->nullable();
            $table->string('sslmethod', 30)->nullable();
            $table->string('auth_type', 20)->nullable();
            $table->tinyInteger('auth_expireson')->nullable();
            $table->string('mail_proxy', 50)->nullable();
            $table->string('connecturl')->nullable();
            $table->string('searchfor', 10)->nullable();
            $table->string('markas', 10)->nullable();
            $table->integer('isvalid')->nullable();
            $table->string('scanfrom', 10)->nullable()->default('ALL');
            $table->string('time_zone', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailscanner');
    }
};
