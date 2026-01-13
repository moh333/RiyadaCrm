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
        Schema::create('vtiger_ticketcomments', function (Blueprint $table) {
            $table->integer('commentid', true);
            $table->integer('ticketid')->nullable()->index('ticketcomments_ticketid_idx');
            $table->text('comments')->nullable();
            $table->integer('ownerid')->default(0);
            $table->string('ownertype', 10)->nullable();
            $table->dateTime('createdtime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_ticketcomments');
    }
};
