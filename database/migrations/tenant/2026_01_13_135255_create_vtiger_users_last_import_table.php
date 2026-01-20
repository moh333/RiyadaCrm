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
        if (Schema::hasTable('vtiger_users_last_import')) {
            return;
        }
        Schema::create('vtiger_users_last_import', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('assigned_user_id', 36)->nullable()->index('idx_user_id');
            $table->string('bean_type', 36)->nullable();
            $table->string('bean_id', 36)->nullable();
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_users_last_import');
    }
};
