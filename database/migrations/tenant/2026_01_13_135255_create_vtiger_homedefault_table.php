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
        if (Schema::hasTable('vtiger_homedefault')) {
            return;
        }
        Schema::create('vtiger_homedefault', function (Blueprint $table) {
            $table->integer('stuffid')->default(0)->primary();
            $table->string('hometype', 30);
            $table->integer('maxentries')->nullable();
            $table->string('setype', 30)->nullable();

            $table->index(['stuffid'], 'stuff_stuffid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homedefault');
    }
};
