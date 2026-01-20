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
        if (Schema::hasTable('vtiger_mailmanager_mailrecord')) {
            return;
        }
        Schema::create('vtiger_mailmanager_mailrecord', function (Blueprint $table) {
            $table->integer('userid')->nullable();
            $table->string('mfrom')->nullable();
            $table->string('mto')->nullable();
            $table->string('mcc', 500)->nullable();
            $table->string('mbcc', 500)->nullable();
            $table->string('mdate', 20)->nullable();
            $table->string('msubject', 500)->nullable();
            $table->text('mbody')->nullable();
            $table->string('mcharset', 10)->nullable();
            $table->integer('misbodyhtml')->nullable();
            $table->integer('mplainmessage')->nullable();
            $table->integer('mhtmlmessage')->nullable();
            $table->string('muniqueid', 500)->nullable();
            $table->integer('mbodyparsed')->nullable();
            $table->integer('muid')->nullable();
            $table->integer('lastsavedtime')->nullable();
            $table->string('folder', 250)->nullable();
            $table->string('mfolder', 250)->nullable();

            $table->index(['userid', 'lastsavedtime'], 'userid_lastsavedtime_idx');
            $table->index(['userid', 'muid'], 'userid_muid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailmanager_mailrecord');
    }
};
