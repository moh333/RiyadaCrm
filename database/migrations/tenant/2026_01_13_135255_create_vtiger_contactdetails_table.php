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
        Schema::create('vtiger_contactdetails', function (Blueprint $table) {
            $table->integer('contactid')->default(0)->primary();
            $table->string('contact_no', 100);
            $table->integer('accountid')->nullable()->index('contactdetails_accountid_idx');
            $table->string('salutation', 200)->nullable();
            $table->string('firstname', 40)->nullable();
            $table->string('lastname', 80);
            $table->string('email', 100)->nullable()->index('email_idx');
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('department', 30)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('reportsto', 30)->nullable();
            $table->string('training', 50)->nullable();
            $table->string('usertype', 50)->nullable();
            $table->string('contacttype', 50)->nullable();
            $table->string('otheremail', 100)->nullable();
            $table->string('secondaryemail', 100)->nullable();
            $table->string('donotcall', 3)->nullable();
            $table->string('emailoptout', 3)->nullable()->default('0');
            $table->string('imagename', 150)->nullable();
            $table->string('reference', 3)->nullable();
            $table->string('notify_owner', 3)->nullable()->default('0');
            $table->string('isconvertedfromlead', 3)->nullable()->default('0');
            $table->string('tags', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_contactdetails');
    }
};
