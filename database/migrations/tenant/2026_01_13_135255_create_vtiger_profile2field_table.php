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
        if (Schema::hasTable('vtiger_profile2field')) {
            return;
        }
        Schema::create('vtiger_profile2field', function (Blueprint $table) {
            $table->integer('profileid');
            $table->integer('tabid')->nullable();
            $table->integer('fieldid');
            $table->integer('visible')->nullable();
            $table->integer('readonly')->nullable();

            $table->primary(['profileid', 'fieldid']);
            $table->index(['profileid', 'tabid'], 'profile2field_profileid_tabid_fieldname_idx');
            $table->index(['tabid', 'profileid'], 'profile2field_tabid_profileid_idx');
            $table->index(['visible', 'profileid'], 'profile2field_visible_profileid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile2field');
    }
};
