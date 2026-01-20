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
        if (Schema::hasTable('vtiger_salesmanattachmentsrel')) {
            return;
        }
        Schema::create('vtiger_salesmanattachmentsrel', function (Blueprint $table) {
            $table->integer('smid')->default(0)->index('salesmanattachmentsrel_smid_idx');
            $table->integer('attachmentsid')->default(0)->index('salesmanattachmentsrel_attachmentsid_idx');

            $table->primary(['smid', 'attachmentsid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_salesmanattachmentsrel');
    }
};
