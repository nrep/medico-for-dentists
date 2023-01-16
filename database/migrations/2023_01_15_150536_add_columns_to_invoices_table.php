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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_id')->nullable()->after('session_id');
            $table->json("specific_data")->nullable()->after('discount_id');

            $table->foreign("discount_id")->references("id")->on("discounts");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);

            $table->dropColumn('discount_id');
            $table->dropColumn("specific_data");
        });
    }
};
