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
        Schema::create('vtiger_relatedlists', function (Blueprint $table) {
            $table->integer('relation_id')->primary();
            $table->integer('tabid')->nullable();
            $table->integer('related_tabid')->nullable();
            $table->string('name', 100)->nullable();
            $table->integer('sequence')->nullable();
            $table->string('label', 100)->nullable();
            $table->integer('presence')->default(0);
            $table->string('actions', 50)->default('');
            $table->integer('relationfieldid')->nullable();
            $table->string('source', 25)->nullable();
            $table->string('relationtype', 10)->nullable();

            $table->index(['relation_id'], 'relatedlists_relation_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_relatedlists');
    }
};
