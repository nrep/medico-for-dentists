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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->integer("number");
            $table->string("names");
            $table->enum("sex", ["Male","Female"])->nullable();
            $table->string("year_of_birth")->nullable();
            $table->string("phone_number")->nullable();
            $table->date("registration_date")->nullable();
            $table->year("registration_year")->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(["number", "names"]);
            $table->unique(["number", "registration_year"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
