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
        if (Schema::hasTable('vtiger_email_access')) {
            return;
        }
        Schema::create('vtiger_email_access', function (Blueprint $table) {
            $table->integer('crmid')->nullable();
            $table->integer('mailid')->nullable();
            $table->date('accessdate')->nullable();
            $table->dateTime('accesstime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_email_access');
    }
};
