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
        if (Schema::hasTable('vtiger_modcomments')) {
            return;
        }
        Schema::create('vtiger_modcomments', function (Blueprint $table) {
            $table->integer('modcommentsid')->nullable()->index('fk_crmid_vtiger_modcomments');
            $table->text('commentcontent')->nullable();
            $table->integer('related_to')->nullable()->index('relatedto_idx');
            $table->integer('parent_comments')->nullable();
            $table->integer('customer')->nullable();
            $table->integer('userid')->nullable();
            $table->string('reasontoedit', 100)->nullable();
            $table->integer('is_private')->nullable()->default(0);
            $table->string('filename')->nullable();
            $table->integer('related_email_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_modcomments');
    }
};
