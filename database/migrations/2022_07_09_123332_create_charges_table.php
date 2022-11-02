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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("charge_list_charge_type_id");
            $table->string("name");
            $table->double("price");
            $table->date("valid_since")->nullable();
            $table->date("valid_until")->nullable();
            $table->boolean("enabled")->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('charge_list_charge_type_id')->references('id')->on('charge_list_charge_types');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charges');
    }
};
