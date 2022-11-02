<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->integer('SectorId')->nullable();
            $table->unsignedInteger('DistrictCode');
            $table->string('SectorName', 12)->nullable();
            $table->integer('SectorCode')->primary();
            $table->integer('SectorStatus')->nullable();

            $table->foreign('DistrictCode')->references('DistrictCode')->on('districts');
            $table->index(['SectorName']);
            $table->unique(['SectorCode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sectors');
    }
}
