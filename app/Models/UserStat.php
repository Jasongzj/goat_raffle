<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $fillable = [
        'raffle_amount', 'launched_raffle_amount', 'award_amount',
    ];
}
