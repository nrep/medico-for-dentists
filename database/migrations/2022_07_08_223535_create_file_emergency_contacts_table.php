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
        Schema::create('file_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("file_id");
            $table->string("name")->nullable();
            $table->string("phone_number")->nullable();
            $table->boolean("enabled")->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("file_id")->references("id")->on("files");
            $table->index("file_id");
            $table->unique(["file_id", "phone_number"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_emergency_contacts');
    }
};
