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
        Schema::create('vtiger_freetagged_objects', function (Blueprint $table) {
            $table->integer('tag_id')->default(0);
            $table->integer('tagger_id')->default(0);
            $table->integer('object_id')->default(0);
            $table->timestamp('tagged_on')->useCurrent();
            $table->string('module', 100)->nullable();

            $table->index(['tag_id', 'tagger_id', 'object_id'], 'freetagged_objects_tag_id_tagger_id_object_id_idx');
            $table->primary(['tag_id', 'tagger_id', 'object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_freetagged_objects');
    }
};
