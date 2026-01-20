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
        if (Schema::hasTable('vtiger_mailmanager_mailrel')) {
            return;
        }
        Schema::create('vtiger_mailmanager_mailrel', function (Blueprint $table) {
            $table->string('mailuid', 999)->nullable();
            $table->integer('crmid')->nullable();
            $table->integer('emailid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailmanager_mailrel');
    }
};
