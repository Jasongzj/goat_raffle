<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRaffleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raffle', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->string('name')->nullable()->comment('活动标题');
            $table->string('img')->nullable()->comment('活动奖品第一张图');
            $table->unsignedTinyInteger('draw_type')->comment('开奖方式 1 时间 2 人数 3 即时开奖');
            $table->dateTime('draw_time')->comment('开奖时间/截止抽奖时间');
            $table->unsignedInteger('draw_participants')->nullable()->comment('开奖要求人数');
            $table->text('desc')->nullable()->comment('抽奖描述');
            $table->text('context')->nullable()->comment('图文文字内容');
            $table->text('context_img')->nullable()->comment('图文图片');
            $table->unsignedTinyInteger('is_sharable')->default(0)->comment('是否可分享');
            $table->unsignedTinyInteger('award_type')->comment('发奖方式 1 中奖者填写地址 2 中奖者联系发起者');
            $table->unsignedBigInteger('contact_id')->nullable()->comment('中奖者联系方式');
            $table->unsignedTinyInteger('status')->default(0)->comment('开奖状态 0 未开奖 1 已开奖 2 已过期');
            $table->unsignedInteger('current_participants')->default(0)->comment('当前参与人数');
            $table->unsignedTinyInteger('sort')->default(0)->comment('抽奖排序值');
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
        Schema::dropIfExists('raffle');
    }
}
