<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRaffle extends Model
{
    protected $table = 'user_raffle';

    protected $fillable = [
        'user_id', 'raffle_id',
    ];

    public function raffle()
    {
        return $this->belongsTo(Raffle::class, 'raffle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public static function getParticipantsList($raffleId)
    {
        $participants = static::query()->where('user_raffle.raffle_id', $raffleId)
            ->join('users', 'user_raffle.user_id', '=', 'users.id')
            ->select(['users.id', 'users.avatar_url'])
            ->orderByDesc('user_raffle.id')
            ->limit(7)
            ->get();
        return $participants;
    }
}
