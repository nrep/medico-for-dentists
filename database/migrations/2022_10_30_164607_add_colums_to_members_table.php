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
        Schema::connection('legacy_mysql')->table('members', function (Blueprint $table) {
            $table->boolean('migrated')->default(0);
            $table->unsignedBigInteger('migrated_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('legacy_mysql')->table('members', function (Blueprint $table) {
            $table->dropColumn('migrated');
            $table->dropColumn('migrated_to');
        });
    }
};
