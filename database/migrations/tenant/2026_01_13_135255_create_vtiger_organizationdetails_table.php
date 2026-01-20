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
        if (Schema::hasTable('vtiger_organizationdetails')) {
            return;
        }
        Schema::create('vtiger_organizationdetails', function (Blueprint $table) {
            $table->integer('organization_id')->primary();
            $table->string('organizationname', 60)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('code', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('logoname', 50)->nullable();
            $table->text('logo')->nullable();
            $table->string('vatid', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_organizationdetails');
    }
};
