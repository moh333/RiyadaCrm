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
        if (Schema::hasTable('vtiger_mail_accounts')) {
            return;
        }
        Schema::create('vtiger_mail_accounts', function (Blueprint $table) {
            $table->integer('account_id')->primary();
            $table->integer('user_id');
            $table->string('display_name', 50)->nullable();
            $table->string('mail_id', 50)->nullable();
            $table->string('account_name', 50)->nullable();
            $table->string('mail_protocol', 20)->nullable();
            $table->string('mail_username', 50);
            $table->text('mail_password')->nullable();
            $table->string('mail_servername', 50)->nullable();
            $table->string('auth_type', 20)->nullable();
            $table->tinyInteger('auth_expireson')->nullable();
            $table->string('mail_proxy', 50)->nullable();
            $table->integer('box_refresh')->nullable();
            $table->integer('mails_per_page')->nullable();
            $table->string('ssltype', 50)->nullable();
            $table->string('sslmeth', 50)->nullable();
            $table->integer('int_mailer')->nullable()->default(0);
            $table->string('status', 10)->nullable();
            $table->integer('set_default')->nullable();
            $table->string('sent_folder', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mail_accounts');
    }
};
