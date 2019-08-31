<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRaffle extends Model
{
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
}
