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
        Schema::create('vtiger_announcement', function (Blueprint $table) {
            $table->integer('creatorid')->index('announcement_creatorid_idx');
            $table->text('announcement')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('time')->nullable();

            $table->primary(['creatorid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_announcement');
    }
};
