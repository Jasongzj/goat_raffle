<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * 小程序 session_key 缓存前缀
     * @var string
     */
    public static $cacheSessionKeyPrefix = 'user_';

    /**
     * 小程序 session_key 缓存后缀
     * @var string
     */
    public static $cacheSessionKeySuffix = '_session_key:';


    protected $fillable = [
        'openid', 'unionid', 'nick_name', 'avatar_url',
        'gender', 'city', 'province', 'country',
    ];

    /**
     * 用户发起的抽奖
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function raffle()
    {
        return $this->hasMany(Raffle::class);
    }

    /**
     * 用户统计
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stat()
    {
        return $this->hasOne(UserStat::class);
    }

    /**
     * 发起者联系方式
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(UserContact::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
