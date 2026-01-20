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
        if (Schema::hasTable('vtiger_google_sync_fieldmapping')) {
            return;
        }
        Schema::create('vtiger_google_sync_fieldmapping', function (Blueprint $table) {
            $table->string('vtiger_field')->nullable();
            $table->string('google_field')->nullable();
            $table->string('google_field_type')->nullable();
            $table->string('google_custom_label')->nullable();
            $table->integer('user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_google_sync_fieldmapping');
    }
};
