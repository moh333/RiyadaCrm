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
        Schema::create('vtiger_entityname', function (Blueprint $table) {
            $table->integer('tabid')->default(0)->index('entityname_tabid_idx');
            $table->string('modulename', 100)->nullable();
            $table->string('tablename', 100);
            $table->string('fieldname', 150);
            $table->string('entityidfield', 150);
            $table->string('entityidcolumn', 150);

            $table->primary(['tabid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtiger_entityname');
    }
};
