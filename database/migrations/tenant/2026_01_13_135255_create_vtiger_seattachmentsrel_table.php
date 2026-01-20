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
        if (Schema::hasTable('vtiger_seattachmentsrel')) {
            return;
        }
        Schema::create('vtiger_seattachmentsrel', function (Blueprint $table) {
            $table->integer('crmid')->default(0)->index('seattachmentsrel_crmid_idx');
            $table->integer('attachmentsid')->default(0)->index('seattachmentsrel_attachmentsid_idx');

            $table->primary(['crmid', 'attachmentsid']);
            $table->index(['attachmentsid', 'crmid'], 'seattachmentsrel_attachmentsid_crmid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_seattachmentsrel');
    }
};
