<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleWinner extends Model
{
    protected $fillable = [
        'raffle_id', 'award_id', 'user_id',
    ];
}