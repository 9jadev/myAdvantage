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
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->string("customer_id")->nullable();
            $table->string("nationality")->nullable();
            $table->string("state_of_residence")->nullable();
            $table->string("house_address")->nullable();
            $table->string("upliner")->nullable();
            $table->string("community_interest")->nullable();
            $table->string("future_aspiration")->nullable();
            $table->string("discount_preferences")->nullable();
            $table->string("pre_existing_health_condtion")->nullable();
            $table->string("pre_existing_health_condtion_drug")->nullable();
            $table->string("allergy")->nullable();
            $table->string("next_of_kin_name")->nullable();
            $table->string("next_of_kin_phone_number")->nullable();
            $table->string("vbank_account_number")->nullable();
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
        Schema::dropIfExists('kycs');
    }
};
