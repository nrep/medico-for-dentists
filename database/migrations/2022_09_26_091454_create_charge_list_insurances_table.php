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
        Schema::create('charge_list_insurances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insurance_id');
            $table->unsignedBigInteger('charge_list_id');
            $table->date('valid_since');
            $table->date('valid_until')->nullable();
            $table->json("specific_data")->nullable();
            $table->boolean("enabled")->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('insurance_id')->references('id')->on('insurances');
            $table->foreign('charge_list_id')->references('id')->on('charge_lists');
            $table->unique(['charge_list_id', 'insurance_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_list_insurances');
    }
};
