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
        Schema::create('vtiger_loginhistory', function (Blueprint $table) {
            $table->integer('login_id', true);
            $table->string('user_name')->nullable();
            $table->string('user_ip', 25);
            $table->timestamp('logout_time')->nullable();
            $table->dateTime('login_time')->nullable();
            $table->string('status', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_loginhistory');
    }
};
