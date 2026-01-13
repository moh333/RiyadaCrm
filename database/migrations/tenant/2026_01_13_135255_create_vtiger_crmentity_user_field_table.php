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
        Schema::create('vtiger_crmentity_user_field', function (Blueprint $table) {
            $table->integer('recordid');
            $table->integer('userid');
            $table->string('starred', 100)->nullable();

            $table->index(['recordid', 'userid'], 'record_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_crmentity_user_field');
    }
};
