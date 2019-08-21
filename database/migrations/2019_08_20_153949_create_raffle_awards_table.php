<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRaffleAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raffle_awards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('raffle_id');
            $table->string('name')->comment('奖项名称');
            $table->string('img')->nullable()->comment('奖项图片');
            $table->unsignedInteger('amount')->comment('奖品数量');
            $table->unsignedTinyInteger('index')->default(0)->comment('奖项排序');
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
        Schema::dropIfExists('raffle_awards');
    }
}
