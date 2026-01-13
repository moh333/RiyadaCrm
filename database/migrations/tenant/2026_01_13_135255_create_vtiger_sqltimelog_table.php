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
        Schema::create('vtiger_sqltimelog', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('type', 10)->nullable();
            $table->text('data')->nullable();
            $table->decimal('started', 20, 6)->nullable();
            $table->decimal('ended', 20, 6)->nullable();
            $table->dateTime('loggedon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_sqltimelog');
    }
};
