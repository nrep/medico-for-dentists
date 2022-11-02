<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->unsignedInteger('DistrictCode')->primary();
            $table->string('DistrictId', 10)->nullable();
            $table->integer('ProvinceCode');
            $table->string('DistrictName', 12)->nullable();
            $table->string('DistrictStatus', 14)->nullable();

            $table->foreign('ProvinceCode')->references('provincecode')->on('provinces');
            $table->index(['DistrictName']);
            $table->unique(['DistrictCode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('districts');
    }
}
