<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleAward extends Model
{
    protected $fillable = [
        'name', 'img', 'amount', 'index',
    ];
}
