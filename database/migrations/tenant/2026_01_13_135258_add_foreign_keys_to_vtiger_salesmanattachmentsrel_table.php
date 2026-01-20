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
        Schema::table('vtiger_salesmanattachmentsrel', function (Blueprint $table) {
            $table->foreign(['attachmentsid'], 'fk_2_vtiger_salesmanattachmentsrel')->references(['attachmentsid'])->on('vtiger_attachments')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vtiger_salesmanattachmentsrel', function (Blueprint $table) {
            $table->dropForeign('fk_2_vtiger_salesmanattachmentsrel');
        });
    }
};
