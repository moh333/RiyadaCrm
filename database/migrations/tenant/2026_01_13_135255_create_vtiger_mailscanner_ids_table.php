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
        Schema::create('vtiger_mailscanner_ids', function (Blueprint $table) {
            $table->integer('scannerid')->nullable();
            $table->string('messageid', 512)->nullable();
            $table->integer('crmid')->nullable()->index('messageids_crmid_idx');
            $table->text('refids')->nullable();

            $table->index(['scannerid', 'messageid'], 'scanner_message_ids_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_mailscanner_ids');
    }
};
