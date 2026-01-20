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
        if (Schema::hasTable('vtiger_customerportal_settings')) {
            return;
        }
        Schema::create('vtiger_customerportal_settings', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('url', 250)->nullable();
            $table->integer('default_assignee')->nullable();
            $table->integer('support_notification')->nullable();
            $table->text('announcement')->nullable();
            $table->text('shortcuts')->nullable();
            $table->text('widgets')->nullable();
            $table->text('charts')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_customerportal_settings');
    }
};
