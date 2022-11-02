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
        Schema::create('charge_list_charge_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('charge_list_id');
            $table->unsignedBigInteger('charge_type_id');
            $table->date('valid_since');
            $table->date('valid_until')->nullable();
            $table->boolean('enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('charge_list_id')->references('id')->on('charge_lists');
            $table->foreign('charge_type_id')->references('id')->on('charge_types');

            $table->unique(['charge_list_id', 'charge_type_id']);
            $table->index(['charge_list_id', 'charge_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_list_charge_types');
    }
};
