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
        Schema::create('vtiger_faqcomments', function (Blueprint $table) {
            $table->integer('commentid', true);
            $table->integer('faqid')->nullable()->index('faqcomments_faqid_idx');
            $table->text('comments')->nullable();
            $table->dateTime('createdtime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_faqcomments');
    }
};
