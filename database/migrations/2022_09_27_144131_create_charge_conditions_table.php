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
        Schema::create('charge_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charge_id');
            $table->json('insurances');
            $table->string('context')->default('billing');
            $table->string('type')->default('times-over-period');
            $table->json('condition');
            $table->boolean('enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('charge_id')->references('id')->on('charges');
            $table->index('charge_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_conditions');
    }
};
