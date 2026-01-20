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
        if (Schema::hasTable('vtiger_attachments')) {
            return;
        }
        Schema::create('vtiger_attachments', function (Blueprint $table) {
            $table->integer('attachmentsid')->index('attachments_attachmentsid_idx');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 100)->nullable();
            $table->text('path')->nullable();
            $table->string('storedname')->nullable();
            $table->string('subject')->nullable();

            $table->primary(['attachmentsid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_attachments');
    }
};
