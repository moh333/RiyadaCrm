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
        if (Schema::hasTable('vtiger_emails_recipientprefs')) {
            return;
        }
        Schema::create('vtiger_emails_recipientprefs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tabid');
            $table->string('prefs')->nullable();
            $table->integer('userid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_emails_recipientprefs');
    }
};
