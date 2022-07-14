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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string("status")->nullable();
            $table->string("customer_id")->nullable();
            $table->string("upliner")->nullable();
            $table->string("referral_code")->nullable();
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->string('email')->unique()->nullable();
            $table->string("phone_number")->nullable();
            $table->string("bvn")->nullable();
            $table->string("id_document")->nullable();
            $table->String('photo_url')->nullable();
            $table->string("password")->nullable();
            $table->dateTime('next_pay')->default(new \DateTime());
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
        Schema::dropIfExists('customers');
    }
};
