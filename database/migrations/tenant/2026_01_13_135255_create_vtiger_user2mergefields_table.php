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
        Schema::create('vtiger_user2mergefields', function (Blueprint $table) {
            $table->integer('userid')->nullable();
            $table->integer('tabid')->nullable();
            $table->integer('fieldid')->nullable();
            $table->integer('visible')->nullable();

            $table->index(['userid', 'tabid'], 'userid_tabid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_user2mergefields');
    }
};
