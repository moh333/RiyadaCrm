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
        Schema::create('vtiger_homedashbd', function (Blueprint $table) {
            $table->integer('stuffid')->default(0)->primary();
            $table->string('dashbdname', 100)->nullable();
            $table->string('dashbdtype', 100)->nullable();

            $table->index(['stuffid'], 'stuff_stuffid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_homedashbd');
    }
};
