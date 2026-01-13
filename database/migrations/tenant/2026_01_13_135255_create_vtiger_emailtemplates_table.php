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
        Schema::create('vtiger_emailtemplates', function (Blueprint $table) {
            $table->string('foldername', 100)->nullable();
            $table->string('templatename', 100)->nullable();
            $table->string('templatepath', 100)->nullable();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->text('body')->nullable();
            $table->integer('deleted')->default(0);
            $table->integer('templateid', true);
            $table->integer('systemtemplate')->default(0);
            $table->string('module', 100)->nullable();

            $table->index(['foldername', 'templatename', 'subject'], 'emailtemplates_foldernamd_templatename_subject_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_emailtemplates');
    }
};
