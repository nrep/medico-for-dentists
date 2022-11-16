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
        Schema::create('fileless_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('names');
            $table->date('date');
            $table->unsignedInteger('done_by');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('done_by')->references('id')->on('users');

            $table->index('names');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fileless_invoices');
    }
};
