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
        if (Schema::hasTable('vtiger_org_share_action_mapping')) {
            return;
        }
        Schema::create('vtiger_org_share_action_mapping', function (Blueprint $table) {
            $table->integer('share_action_id')->primary();
            $table->string('share_action_name', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_org_share_action_mapping');
    }
};
