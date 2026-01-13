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
        Schema::create('vtiger_eventhandlers', function (Blueprint $table) {
            $table->integer('eventhandler_id', true)->unique('eventhandler_idx');
            $table->string('event_name', 100);
            $table->string('handler_path', 400);
            $table->string('handler_class', 100);
            $table->text('cond');
            $table->integer('is_active');
            $table->string('dependent_on')->nullable()->default('[]');

            $table->primary(['eventhandler_id', 'event_name', 'handler_class']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_eventhandlers');
    }
};
