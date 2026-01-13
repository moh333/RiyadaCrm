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
        Schema::create('vtiger_wordtemplates', function (Blueprint $table) {
            $table->integer('templateid')->primary();
            $table->string('filename', 100);
            $table->string('module', 30);
            $table->timestamp('date_entered');
            $table->string('parent_type', 50);
            $table->binary('data');
            $table->text('description')->nullable();
            $table->string('filesize', 50);
            $table->string('filetype', 20);
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_wordtemplates');
    }
};
