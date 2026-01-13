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
        Schema::create('vtiger_mailmanager_mailattachments', function (Blueprint $table) {
            $table->integer('userid')->nullable();
            $table->integer('muid')->nullable();
            $table->string('aname', 100)->nullable();
            $table->integer('lastsavedtime')->nullable();
            $table->integer('attachid');
            $table->string('path', 200);
            $table->string('cid', 100)->nullable();

            $table->index(['userid', 'muid'], 'userid_muid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailmanager_mailattachments');
    }
};
