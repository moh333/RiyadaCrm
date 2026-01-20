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
        if (Schema::hasTable('vtiger_role2profile')) {
            return;
        }
        Schema::create('vtiger_role2profile', function (Blueprint $table) {
            $table->string('roleid');
            $table->integer('profileid');

            $table->primary(['roleid', 'profileid']);
            $table->index(['roleid', 'profileid'], 'role2profile_roleid_profileid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_role2profile');
    }
};
