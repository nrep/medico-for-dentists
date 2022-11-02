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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string("names");
            $table->enum("sex", ["M" => "Male", "F" => "Female"])->nullable();
            $table->string("phone_number")->nullable();
            $table->unsignedBigInteger("employee_category_id");
            $table->enum("degree", ["None", "A2", "A1", "A0", 'Masters', "PhD"])->nullable();
            $table->date("started_at")->nullable();
            $table->json("specific_data")->nullable();
            $table->boolean("enabled")->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("employee_category_id")->references("id")->on("employee_categories");
            $table->unique("names");
            $table->index("names");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
