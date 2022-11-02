<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cells', function (Blueprint $table) {
            $table->integer('CellId')->nullable();
            $table->integer('SectorCode');
            $table->string('CellName', 12)->nullable();
            $table->integer('CellCode')->primary();
            $table->integer('CellStatus')->nullable();

            $table->foreign('SectorCode')->references('SectorCode')->on('sectors');
            $table->index(['CellName']);
            $table->unique(['CellCode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cells');
    }
}
