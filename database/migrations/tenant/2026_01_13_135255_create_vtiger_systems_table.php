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
        if (Schema::hasTable('vtiger_systems')) {
            return;
        }
        Schema::create('vtiger_systems', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('server', 100)->nullable();
            $table->integer('server_port')->nullable();
            $table->string('server_username', 100)->nullable();
            $table->text('server_password')->nullable();
            $table->string('server_type', 20)->nullable();
            $table->string('smtp_auth', 5)->nullable();
            $table->string('smtp_auth_type', 20)->nullable();
            $table->tinyInteger('smtp_auth_expireson')->nullable();
            $table->string('server_path', 256)->nullable();
            $table->string('from_email_field', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_systems');
    }
};
