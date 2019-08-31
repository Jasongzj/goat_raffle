<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    const DRAW_BASE_ON_TIME    = 1;   // 按时间开奖
    const DRAW_BASE_ON_PARTICIPANTS = 2;   // 按人数开奖
    const DRAW_IMMEDIATELY     = 3;   // 即时开奖

    public static $drawTypeMap = [
        self::DRAW_BASE_ON_TIME => '按时间开奖',
        self::DRAW_BASE_ON_PARTICIPANTS => '按参与人数开奖',
        self::DRAW_IMMEDIATELY => '即开即中',
    ];

    const SHIP_TO_DESIGNATED_ADDRESS = 1;
    const CONTACT_INITIATOR_BY_WINNER = 2;

    public static $awardTypeMap = [
        self::SHIP_TO_DESIGNATED_ADDRESS => '按收货地址发货',
        self::CONTACT_INITIATOR_BY_WINNER => '让中奖者联系我',
    ];

    const STATUS_NOT_END = 0;
    const STATUS_ENDED = 1;
    const STATUS_EXPIRED = 2;

    public static $statusMap = [
        self::STATUS_NOT_END => '未开奖',
        self::STATUS_ENDED => '已开奖',
        self::STATUS_EXPIRED => '已过期',
    ];

    protected $table = 'raffle';

    protected $fillable = [
        'name', 'draw_type', 'draw_time', 'draw_participants',
        'desc', 'context', 'is_sharable', 'contact_id',
        'award_type',
    ];

    protected $casts = [
        'is_sharable' => 'boolean',
    ];

    /**
     * 发奖者联系方式
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userContact()
    {
        return $this->belongsTo(UserContact::class, 'contact_id');
    }

    /**
     * 抽奖奖项
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function awards()
    {
        return $this->hasMany(RaffleAward::class);
    }
}
