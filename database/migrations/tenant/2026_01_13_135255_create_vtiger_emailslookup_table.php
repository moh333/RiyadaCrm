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
        if (Schema::hasTable('vtiger_emailslookup')) {
            return;
        }
        Schema::create('vtiger_emailslookup', function (Blueprint $table) {
            $table->integer('crmid')->nullable();
            $table->string('setype', 100)->nullable();
            $table->string('value', 100)->nullable();
            $table->integer('fieldid')->nullable();

            $table->unique(['crmid', 'setype', 'fieldid'], 'emailslookup_crmid_setype_fieldname_uk');
            $table->index(['fieldid', 'setype'], 'emailslookup_fieldid_setype_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_emailslookup');
    }
};
