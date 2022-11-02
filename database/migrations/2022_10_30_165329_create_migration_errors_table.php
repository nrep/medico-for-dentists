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
        Schema::create('migration_errors', function (Blueprint $table) {
            $table->id();
            $table->morphs('from_table');
            $table->nullableMorphs('to_table');
            $table->string('model_title');
            $table->json('data');
            $table->json('error_message')->nullable();
            $table->string('error_title');
            $table->boolean('resolved')->default(false);
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('migration_errors');
    }
};
