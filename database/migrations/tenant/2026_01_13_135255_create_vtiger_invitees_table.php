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
        Schema::create('vtiger_invitees', function (Blueprint $table) {
            $table->integer('activityid');
            $table->integer('inviteeid');
            $table->string('status', 50)->nullable();

            $table->primary(['activityid', 'inviteeid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_invitees');
    }
};
