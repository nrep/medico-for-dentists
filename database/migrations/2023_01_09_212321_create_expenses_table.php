<?php

use Carbon\Carbon;
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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_account_id');
            $table->string('bill_no')->nullable();
            $table->morphs('expenseable');
            $table->unsignedBigInteger('payment_mean_id');
            $table->date('date')->default(Carbon::now());
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('budget_account_id')->references('id')->on('budget_accounts');
            $table->foreign('payment_mean_id')->references('id')->on('payment_means');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
