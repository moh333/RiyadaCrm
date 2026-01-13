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
        Schema::create('vtiger_shippingtaxinfo', function (Blueprint $table) {
            $table->integer('taxid')->primary();
            $table->string('taxname', 50)->nullable()->index('shippingtaxinfo_taxname_idx');
            $table->string('taxlabel', 50)->nullable();
            $table->decimal('percentage', 7, 3)->nullable();
            $table->integer('deleted')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('type', 10)->nullable();
            $table->string('compoundon', 400)->nullable();
            $table->text('regions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_shippingtaxinfo');
    }
};
