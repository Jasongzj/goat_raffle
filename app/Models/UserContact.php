<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    const TYPE_COPY = 1;
    const TYPE_SUBS = 2;

    public static $typeMap = [
        self::TYPE_COPY => '一键复制',
        self::TYPE_SUBS => '快捷关注',
    ];

    const SUBS_WECHAT           = 1;
    const SUBS_OFFICIAL_ACCOUNT = 2;
    const SUBS_WECHAT_GROUP     = 3;
    const SUBS_MINI_PRO         = 4;
    const SUBS_OTHER            = 5;

    public static $subsMap = [
        self::SUBS_WECHAT           => '微信号',
        self::SUBS_OFFICIAL_ACCOUNT => '公众号',
        self::SUBS_WECHAT_GROUP     => '微信群',
        self::SUBS_MINI_PRO         => '小程序',
        self::SUBS_OTHER            => '其他',
    ];

    protected $fillable = [
        'type', 'subs_type', 'content', 'title', 'img',
    ];


}
