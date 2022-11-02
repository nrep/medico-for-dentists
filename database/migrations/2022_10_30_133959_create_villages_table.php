<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVillagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->integer('VillageId')->nullable();
            $table->integer('CellCode');
            $table->string('VillageName', 19)->nullable();
            $table->integer('VillageCode')->primary();
            $table->integer('VillageStatus')->nullable();

            $table->foreign('CellCode')->references('CellCode')->on('cells');
            $table->index(['VillageName']);
            $table->unique(['VillageCode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('villages');
    }
}
