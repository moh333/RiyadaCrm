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
        Schema::create('vtiger_import_maps', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 36);
            $table->string('module', 36);
            $table->binary('content')->nullable();
            $table->integer('has_header')->default(1);
            $table->integer('deleted')->default(0);
            $table->timestamp('date_entered');
            $table->dateTime('date_modified')->nullable();
            $table->string('assigned_user_id', 36)->nullable();
            $table->string('is_published', 3)->default('no');

            $table->index(['assigned_user_id', 'module', 'name', 'deleted'], 'import_maps_assigned_user_id_module_name_deleted_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_import_maps');
    }
};
