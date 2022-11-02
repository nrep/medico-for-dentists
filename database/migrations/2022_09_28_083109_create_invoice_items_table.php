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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_day_id');
            $table->unsignedBigInteger('charge_id');
            $table->integer('quantity');
            $table->double('sold_at');
            $table->double('total_price');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_day_id')->references('id')->on('invoice_days');
            $table->foreign('charge_id')->references('id')->on('charges');

            $table->index(['invoice_day_id', 'charge_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};
