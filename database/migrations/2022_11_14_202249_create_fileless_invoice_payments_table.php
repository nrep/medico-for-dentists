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
        Schema::create('fileless_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fileless_invoice_id');
            $table->unsignedBigInteger('payment_mean_id');
            $table->double('amount');
            $table->unsignedInteger('done_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('fileless_invoice_id')->references('id')->on('fileless_invoices');
            $table->foreign('payment_mean_id')->references('id')->on('payment_means');
            $table->foreign('done_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fileless_invoice_payments');
    }
};
