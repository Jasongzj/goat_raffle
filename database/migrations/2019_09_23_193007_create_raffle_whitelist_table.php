<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRaffleWhitelistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raffle_whitelist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('raffle_id')->comment('抽奖id');
            $table->unsignedBigInteger('award_id')->comment('奖项id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
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
        Schema::dropIfExists('raffle_whitelist');
    }
}
