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
        if (Schema::hasTable('vtiger_notes')) {
            return;
        }
        Schema::create('vtiger_notes', function (Blueprint $table) {
            $table->integer('notesid')->default(0)->index('notes_notesid_idx');
            $table->string('note_no', 100);
            $table->string('title', 50)->index('notes_title_idx');
            $table->string('filename', 200)->nullable();
            $table->text('notecontent')->nullable();
            $table->integer('folderid')->default(1);
            $table->string('filetype', 50)->nullable();
            $table->string('filelocationtype', 5)->nullable();
            $table->integer('filedownloadcount')->nullable();
            $table->integer('filestatus')->nullable();
            $table->integer('filesize')->default(0);
            $table->string('fileversion', 50)->nullable();
            $table->string('tags', 1)->nullable();

            $table->primary(['notesid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_notes');
    }
};
