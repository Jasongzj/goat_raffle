<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleWhitelist extends Model
{
    protected $table = 'raffle_whitelist';

    protected $fillable = ['raffle_id', 'award_id', 'user_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function awards()
    {
        return $this->belongsTo(RaffleAward::class, 'award_id');
    }
}
