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
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(["number", "names"]);
            $table->index(["number", "full_number","names", "phone_number"]);
        });

        Schema::table('charge_lists', function (Blueprint $table) {
            $table->index("title");
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->index(['name', 'price']);
        });

        Schema::table('file_emergency_contacts', function (Blueprint $table) {
            $table->dropIndex(['file_id']);
            $table->index(['name', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(["number", "full_number","names", "phone_number"]);
            $table->index(["number", "names"]);
        });

        Schema::table('charge_lists', function (Blueprint $table) {
            $table->dropIndex(["title"]);
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->dropIndex(['name', 'price']);
            $table->index(['name']);
        });

        Schema::table('file_emergency_contacts', function (Blueprint $table) {
            $table->dropIndex(['name', 'phone_number']);
            $table->index('file_id');
        });
    }
};
