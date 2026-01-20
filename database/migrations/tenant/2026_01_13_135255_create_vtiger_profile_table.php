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
        if (Schema::hasTable('vtiger_profile')) {
            return;
        }
        Schema::create('vtiger_profile', function (Blueprint $table) {
            $table->integer('profileid', true);
            $table->string('profilename', 50);
            $table->text('description')->nullable();
            $table->integer('directly_related_to_role')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_profile');
    }
};
