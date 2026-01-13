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
        Schema::create('vtiger_vendorcontactrel', function (Blueprint $table) {
            $table->integer('vendorid')->default(0)->index('vendorcontactrel_vendorid_idx');
            $table->integer('contactid')->default(0)->index('vendorcontactrel_contact_idx');

            $table->primary(['vendorid', 'contactid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_vendorcontactrel');
    }
};
