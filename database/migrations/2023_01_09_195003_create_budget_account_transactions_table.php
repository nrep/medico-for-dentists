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
        Schema::create('budget_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_account_id');
            $table->unsignedBigInteger('budget_source_id')->nullable();
            $table->enum('nature', ['credit', 'debit']);
            $table->double('amount');
            $table->double('balance')->nullable();
            $table->text('description')->nullable();
            $table->date('date');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('budget_account_id')->references('id')->on('budget_accounts');
            $table->foreign('budget_source_id')->references('id')->on('budget_sources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_account_transactions');
    }
};
