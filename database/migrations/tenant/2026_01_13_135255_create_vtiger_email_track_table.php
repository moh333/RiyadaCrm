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
        if (Schema::hasTable('vtiger_email_track')) {
            return;
        }
        Schema::create('vtiger_email_track', function (Blueprint $table) {
            $table->integer('crmid')->nullable();
            $table->integer('mailid')->nullable();
            $table->integer('access_count')->nullable();
            $table->integer('click_count')->default(0);

            $table->unique(['crmid', 'mailid'], 'link_tabidtype_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_email_track');
    }
};
