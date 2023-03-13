<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_insurances', function (Blueprint $table) {
            $table->dropUnique(["file_id", "insurance_id"]);
            $table->unique(["file_id", "insurance_id", "specific_data"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_insurances', function (Blueprint $table) {
            $table->dropUnique(["file_id", "insurance_id", "specific_data"]);
            $table->unique(["file_id", "insurance_id"]);
        });
    }
};
