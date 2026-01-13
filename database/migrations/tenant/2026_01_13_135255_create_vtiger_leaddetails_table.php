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
        Schema::create('vtiger_leaddetails', function (Blueprint $table) {
            $table->integer('leadid')->primary();
            $table->string('lead_no', 100);
            $table->string('email', 100)->nullable()->index('email_idx');
            $table->string('interest', 50)->nullable();
            $table->string('firstname', 40)->nullable();
            $table->string('salutation', 200)->nullable();
            $table->string('lastname', 80);
            $table->string('company', 100);
            $table->decimal('annualrevenue', 25, 8)->nullable();
            $table->string('industry', 200)->nullable();
            $table->string('campaign', 30)->nullable();
            $table->string('rating', 200)->nullable();
            $table->string('leadstatus', 200)->nullable();
            $table->string('leadsource', 200)->nullable();
            $table->integer('converted')->nullable()->default(0);
            $table->string('designation', 50)->nullable()->default('SalesMan');
            $table->string('licencekeystatus', 50)->nullable();
            $table->string('space', 250)->nullable();
            $table->text('comments')->nullable();
            $table->string('priority', 50)->nullable();
            $table->string('demorequest', 50)->nullable();
            $table->string('partnercontact', 50)->nullable();
            $table->string('productversion', 20)->nullable();
            $table->string('product', 50)->nullable();
            $table->date('maildate')->nullable();
            $table->date('nextstepdate')->nullable();
            $table->string('fundingsituation', 50)->nullable();
            $table->string('purpose', 50)->nullable();
            $table->string('evaluationstatus', 50)->nullable();
            $table->date('transferdate')->nullable();
            $table->string('revenuetype', 50)->nullable();
            $table->integer('noofemployees')->nullable();
            $table->string('secondaryemail', 100)->nullable();
            $table->integer('assignleadchk')->nullable()->default(0);
            $table->string('emailoptout', 3)->nullable();
            $table->string('tags', 1)->nullable();

            $table->index(['converted', 'leadstatus'], 'leaddetails_converted_leadstatus_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_leaddetails');
    }
};
