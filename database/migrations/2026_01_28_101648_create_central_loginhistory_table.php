<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vtiger_loginhistory')) {
            return;
        }
        Schema::create('vtiger_loginhistory', function (Blueprint $table) {
            $table->id('login_id');
            $table->string('user_name')->nullable();
            $table->string('user_ip', 45); // Support IPv6
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
