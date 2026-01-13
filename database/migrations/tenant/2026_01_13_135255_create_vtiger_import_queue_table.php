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
        Schema::create('vtiger_import_queue', function (Blueprint $table) {
            $table->integer('importid')->primary();
            $table->integer('userid');
            $table->integer('tabid');
            $table->text('field_mapping')->nullable();
            $table->text('default_values')->nullable();
            $table->integer('merge_type')->nullable();
            $table->text('merge_fields')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->integer('lineitem_currency_id')->nullable();
            $table->integer('paging')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_import_queue');
    }
};
