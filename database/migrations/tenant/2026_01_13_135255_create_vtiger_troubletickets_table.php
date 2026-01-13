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
        Schema::create('vtiger_troubletickets', function (Blueprint $table) {
            $table->integer('ticketid')->primary();
            $table->string('ticket_no', 100);
            $table->string('groupname', 100)->nullable();
            $table->string('parent_id', 100)->nullable();
            $table->string('product_id', 100)->nullable();
            $table->string('priority', 200)->nullable();
            $table->string('severity', 200)->nullable();
            $table->string('status', 200)->nullable()->index('troubletickets_status_idx');
            $table->string('category', 200)->nullable();
            $table->string('title');
            $table->text('solution')->nullable();
            $table->text('update_log')->nullable();
            $table->integer('version_id')->nullable();
            $table->decimal('hours', 25, 8)->nullable();
            $table->decimal('days', 25, 8)->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('tags', 1)->nullable();

            $table->index(['ticketid'], 'troubletickets_ticketid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_troubletickets');
    }
};
