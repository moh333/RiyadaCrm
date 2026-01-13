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
        Schema::create('vtiger_relatedlists_rb', function (Blueprint $table) {
            $table->integer('entityid')->nullable();
            $table->string('action', 50)->nullable();
            $table->string('rel_table', 200)->nullable();
            $table->string('rel_column', 200)->nullable();
            $table->string('ref_column', 200)->nullable();
            $table->text('related_crm_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_relatedlists_rb');
    }
};
