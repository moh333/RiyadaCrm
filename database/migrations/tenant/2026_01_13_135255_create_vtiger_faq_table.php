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
        Schema::create('vtiger_faq', function (Blueprint $table) {
            $table->integer('id', true)->index('faq_id_idx');
            $table->string('faq_no', 100);
            $table->string('product_id', 100)->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('category', 200);
            $table->string('status', 200);
            $table->string('tags', 1)->nullable();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_faq');
    }
};
