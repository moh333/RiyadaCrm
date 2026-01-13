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
        Schema::create('vtiger_role', function (Blueprint $table) {
            $table->string('roleid')->primary();
            $table->string('rolename', 200)->nullable();
            $table->string('parentrole')->nullable();
            $table->integer('depth')->nullable();
            $table->integer('allowassignedrecordsto')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_role');
    }
};
