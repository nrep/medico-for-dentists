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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("insurance_id");
            $table->string("display_name")->default("0%");
            $table->double("discount")->default(0);
            $table->double("insured_pays")->default(100);
            $table->boolean("enabled")->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("insurance_id")->references("id")->on("insurances")->onDelete("cascade");
            $table->unique(["insurance_id", "discount", "insured_pays"]);
            $table->index("discount");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
