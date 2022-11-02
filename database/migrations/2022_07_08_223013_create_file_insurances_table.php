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
        Schema::create('file_insurances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("file_id");
            $table->unsignedBigInteger("insurance_id");
            $table->json("specific_data")->nullable();
            $table->boolean("enabled")->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("file_id")->references("id")->on("files");
            $table->foreign("insurance_id")->references("id")->on("insurances");

            $table->index("file_id");

            $table->unique(["file_id", "insurance_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_insurances');
    }
};
