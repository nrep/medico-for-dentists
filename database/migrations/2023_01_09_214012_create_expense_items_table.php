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
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_id');
            $table->unsignedBigInteger('budget_line_id');
            $table->double('amount');
            $table->string('reason');
            $table->boolean('is_ebm_billed')->default(false);
            $table->string('ebm_bill_number')->nullable();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('expense_id')->references('id')->on('expenses');
            $table->foreign('budget_line_id')->references('id')->on('budget_lines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_items');
    }
};
