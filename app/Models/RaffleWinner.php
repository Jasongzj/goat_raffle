<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleWinner extends Model
{
    protected $fillable = [
        'raffle_id', 'award_id', 'user_id', 'address', 'remark'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getListByAwardIds($awardIds)
    {
        $winnerList = static::query()
            ->whereIn('award_id', $awardIds)
            ->with('users:id,avatar_url,nick_name')
            ->select(['raffle_winners.award_id', 'raffle_winners.user_id', 'raffle_winners.address', 'raffle_winners.message'])
            ->orderBy('award_id')
            ->get();
        return $winnerList;
    }
}
