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
        Schema::create('vtiger_extnstore_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('username', 50)->nullable();
            $table->string('password', 75)->nullable();
            $table->string('instanceurl')->nullable();
            $table->dateTime('createdon')->nullable();
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_extnstore_users');
    }
};
