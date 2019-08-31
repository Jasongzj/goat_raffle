<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('type')->comment('联系类型 1 一键复制 2 快捷关注');
            $table->unsignedTinyInteger('subs_type')->nullable()->comment('快捷关注类型 1 微信号 2 公众号 3 微信群 4 小程序 5 其他');
            $table->string('title')->nullable()->comment('引导文案');
            $table->string('content')->comment('正文显示内容');
            $table->string('img')->nullable()->comment('二维码图片');
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
        Schema::dropIfExists('user_contacts');
    }
}
