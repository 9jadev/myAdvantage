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
        Schema::table('payments', function (Blueprint $table) {
            $table->string("firstname")->after("status")->nullable();
            $table->string("lastname")->after("firstname")->nullable();
            $table->string("image")->after("lastname")->nullable();
            $table->string("bank_name")->after("image")->nullable();
            $table->string("bank_account")->after("bank_name")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn("firstname");
            $table->dropColumn("lastname");
            $table->dropColumn("image");
            $table->dropColumn("bank_name");
            $table->dropColumn("bank_account");
        });
    }
};
