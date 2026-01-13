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
        Schema::create('vtiger_emaildetails', function (Blueprint $table) {
            $table->integer('emailid')->primary();
            $table->string('from_email', 50)->default('');
            $table->text('to_email');
            $table->text('cc_email');
            $table->text('bcc_email');
            $table->string('assigned_user_email', 50)->default('');
            $table->text('idlists');
            $table->string('email_flag', 50)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_emaildetails');
    }
};
