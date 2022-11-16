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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("file_insurance_id");
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->date("date");
            $table->unsignedInteger("done_by");
            $table->json("specific_data")->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("file_insurance_id")->references("id")->on("file_insurances");
            $table->foreign("discount_id")->references("id")->on("discounts");
            $table->foreign("done_by")->references("id")->on("users");
            $table->index(["file_insurance_id", "date", "done_by"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
