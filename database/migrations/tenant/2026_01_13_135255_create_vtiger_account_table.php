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
        Schema::create('vtiger_account', function (Blueprint $table) {
            $table->integer('accountid')->default(0)->primary();
            $table->string('account_no', 100);
            $table->string('accountname', 100);
            $table->integer('parentid')->nullable()->default(0);
            $table->string('account_type', 200)->nullable()->index('account_account_type_idx');
            $table->string('industry', 200)->nullable();
            $table->decimal('annualrevenue', 25, 8)->nullable();
            $table->string('rating', 200)->nullable();
            $table->string('ownership', 50)->nullable();
            $table->string('siccode', 50)->nullable();
            $table->string('tickersymbol', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('otherphone', 30)->nullable();
            $table->string('email1', 100)->nullable();
            $table->string('email2', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('fax', 30)->nullable();
            $table->integer('employees')->nullable()->default(0);
            $table->string('emailoptout', 3)->nullable()->default('0');
            $table->string('notify_owner', 3)->nullable()->default('0');
            $table->string('isconvertedfromlead', 3)->nullable()->default('0');
            $table->string('tags', 1)->nullable();

            $table->index(['email1', 'email2'], 'email_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_account');
    }
};
