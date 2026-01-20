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
        if (Schema::hasTable('vtiger_portalinfo')) {
            return;
        }
        Schema::create('vtiger_portalinfo', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('user_name', 50)->nullable();
            $table->string('user_password')->nullable();
            $table->string('type', 5)->nullable();
            $table->string('cryptmode', 20)->nullable();
            $table->dateTime('last_login_time')->nullable();
            $table->dateTime('login_time')->nullable();
            $table->dateTime('logout_time')->nullable();
            $table->integer('isactive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_portalinfo');
    }
};
