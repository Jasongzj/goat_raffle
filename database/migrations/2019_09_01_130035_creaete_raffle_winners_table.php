<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaeteRaffleWinnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raffle_winners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('raffle_id');
            $table->unsignedBigInteger('award_id');
            $table->unsignedBigInteger('user_id');
            $table->text('address')->default('')->comment('中奖发货地址');
            $table->string('message')->default('')->comment('用户留言');
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
        Schema::dropIfExists('raffle_winners');
    }
}
